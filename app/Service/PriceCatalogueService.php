<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Enums\EnumUserRole;
use App\Enums\PriceStatus;
use App\Models\Country;
use App\Models\DefaultPrice;
use App\Models\DefaultPriceHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Owns the price catalogue review workflow (docs/PRICE_CATALOGUE_DESIGN.md):
 * drafts, activation, the ±30 % second review and the audit trail.
 *
 * There is one row per (country, item) and its `status` is the row's lifecycle.
 * An edit makes the row a draft; a reviewer activates it. A change bigger than
 * the configured threshold stops at `pending_second_review` until a different
 * admin confirms it (decision D4).
 */
class PriceCatalogueService
{
    /**
     * Create a row as a draft.
     */
    public function createDraft(array $data, ?User $actor = null): DefaultPrice
    {
        $data = $this->withDefaults($data);

        $exists = DefaultPrice::query()
            ->where('country', $data['country'])
            ->where('item', $data['item'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'item' => 'A price for this item already exists in this country. Edit it instead.',
            ]);
        }

        $data['status'] = PriceStatus::Draft->value;
        $data['updated_by'] = $actor?->id;
        $data['submitted_by'] = null;

        $price = DefaultPrice::create($data);

        $this->record($price, 'created', $actor, null, $this->snapshot($price));

        return $price;
    }

    /**
     * Update a row and put it back into draft. The previous values are kept in
     * the history so the ±30 % test and the audit trail still work.
     */
    public function saveDraft(DefaultPrice $price, array $data, ?User $actor = null): DefaultPrice
    {
        $data = $this->withDefaults($data + [
            'country' => $price->country,
            'item' => $price->item,
        ]);

        $movedTo = DefaultPrice::query()
            ->where('country', $data['country'])
            ->where('item', $data['item'])
            ->whereKeyNot($price->getKey())
            ->exists();

        if ($movedTo) {
            throw ValidationException::withMessages([
                'item' => 'A price for this item already exists in this country.',
            ]);
        }

        $old = $this->snapshot($price);

        // Never let a normal edit jump straight back to active.
        unset($data['status'], $data['submitted_by']);
        $price->fill($data);
        $price->status = PriceStatus::Draft;
        $price->submitted_by = null;
        $price->updated_by = $actor?->id;

        if ($price->isDirty()) {
            $price->save();
            $this->record($price, 'updated', $actor, $old, $this->snapshot($price));
        }

        return $price;
    }

    /**
     * Activate a draft, or hold it for a second admin when it moves the price
     * by more than the configured threshold.
     */
    public function activate(DefaultPrice $price, User $actor, ?string $note = null): DefaultPrice
    {
        if ($price->status === PriceStatus::Active) {
            return $price;
        }

        if ($price->status === PriceStatus::PendingSecondReview) {
            throw ValidationException::withMessages([
                'status' => 'This change must be confirmed by a second admin.',
            ]);
        }

        $this->assertCanReview($actor, $price->country);

        $previous = $this->previousActiveSnapshot($price);
        $current = $this->snapshot($price);

        if ($previous !== null && $this->needsSecondReview($previous, $current)) {
            $price->status = PriceStatus::PendingSecondReview;
            $price->submitted_by = $actor->id;
            $price->updated_by = $actor->id;
            $price->save();

            $this->record($price, 'second_review_requested', $actor, $previous, $current, $note);

            return $price;
        }

        $this->markActive($price, $actor);
        $this->record($price, 'activated', $actor, $previous, $this->snapshot($price), $note);

        return $price;
    }

    /**
     * Take a price out of service: an active price, or one waiting for a second
     * review, goes back to draft. A draft is never served to clients, so the
     * item is no longer priced (and a fertilizer with no other active price drops
     * out of the fertilizer list) until someone activates it again. The values
     * are kept; the engine removes the price at its next full sync.
     */
    public function deactivate(DefaultPrice $price, User $actor, ?string $note = null): DefaultPrice
    {
        if ($price->status === PriceStatus::Draft) {
            return $price;
        }

        $this->assertCanReview($actor, $price->country);

        $old = $this->snapshot($price);

        $price->status = PriceStatus::Draft;
        $price->submitted_by = null;
        $price->updated_by = $actor->id;
        $price->save();

        $this->record($price, 'deactivated', $actor, $old, $this->snapshot($price), $note);

        return $price;
    }

    /**
     * Confirm a change that needed a second review. The confirming admin must
     * not be the one who submitted it.
     */
    public function confirm(DefaultPrice $price, User $actor, ?string $note = null): DefaultPrice
    {
        if ($price->status !== PriceStatus::PendingSecondReview) {
            throw ValidationException::withMessages([
                'status' => 'Only a price awaiting a second review can be confirmed.',
            ]);
        }

        $this->assertCanReview($actor, $price->country);

        if ($price->submitted_by !== null && $price->submitted_by === $actor->id) {
            throw ValidationException::withMessages([
                'status' => 'A second admin must confirm this change; it cannot be the admin who submitted it.',
            ]);
        }

        $previous = $this->previousActiveSnapshot($price);
        $this->markActive($price, $actor);
        $this->record($price, 'confirmed', $actor, $previous, $this->snapshot($price), $note);

        return $price;
    }

    /**
     * Whether the actor may review this country. Until a country has reviewers
     * assigned, any admin may review.
     */
    public function assertCanReview(User $actor, string $country): void
    {
        if ($actor->role !== EnumUserRole::Admin) {
            throw ValidationException::withMessages([
                'status' => 'Only an admin can review prices.',
            ]);
        }

        $reviewers = Country::query()
            ->where('code', $country)
            ->first()
            ?->reviewers()
            ->pluck('users.id')
            ->all() ?? [];

        if ($reviewers !== [] && !in_array($actor->id, $reviewers, true)) {
            throw ValidationException::withMessages([
                'status' => 'You are not a reviewer for this country.',
            ]);
        }
    }

    /**
     * Fill unit, bag size and currency when the caller left them out.
     */
    public function withDefaults(array $data): array
    {
        $country = (string)($data['country'] ?? '');
        $item = (string)($data['item'] ?? '');

        $unit = $data['unit'] ?? null;
        if ($unit === null || $unit === '') {
            // The unit the item is priced in (price_items); anything unlisted is fertiliser, per bag
            $unit = DB::table('price_items')->where('item', $item)->value('unit') ?? 'per_bag';
        }
        $data['unit'] = $unit;

        if ($unit === 'per_bag') {
            $data['bag_kg'] = $data['bag_kg'] ?? config('price_catalogue.default_bag_kg');
        } else {
            $data['bag_kg'] = null;
        }

        if (($data['currency'] ?? null) === null || ($data['currency'] ?? '') === '') {
            $data['currency'] = DB::table('currencies')
                ->where('country_code', $country)
                ->value('currency_code');
        }

        return $data;
    }

    /**
     * The most recent snapshot of this row while it was active, used to test
     * the size of a change.
     */
    public function previousActiveSnapshot(DefaultPrice $price): ?array
    {
        foreach ($price->history()->latest('id')->get() as $entry) {
            foreach ([$entry->new_values, $entry->old_values] as $snapshot) {
                if (is_array($snapshot) && ($snapshot['status'] ?? null) === PriceStatus::Active->value) {
                    return $snapshot;
                }
            }
        }

        return null;
    }

    public function needsSecondReview(array $previous, array $current): bool
    {
        $threshold = (float)config('price_catalogue.second_review_threshold', 0.30);

        foreach (['price', 'subsidised_price'] as $field) {
            $old = $previous[$field] ?? null;
            $new = $current[$field] ?? null;

            if ($old === null || $new === null || (float)$old == 0.0) {
                continue;
            }

            if (abs((float)$new - (float)$old) / abs((float)$old) > $threshold) {
                return true;
            }
        }

        return false;
    }

    /**
     * A stable, JSON-friendly view of a row for the history table.
     */
    public function snapshot(DefaultPrice $price): array
    {
        return [
            'country' => $price->country,
            'region' => $price->region,
            'item' => $price->item,
            'price' => $price->price,
            'subsidised_price' => $price->subsidised_price,
            'unit' => $price->unit,
            'bag_kg' => $price->bag_kg,
            'currency' => $price->currency,
            'min_price' => $price->min_price,
            'max_price' => $price->max_price,
            'status' => $price->status?->value ?? $price->status,
            'source' => $price->source,
            'source_date' => $price->source_date?->toDateString(),
            'confidence' => $price->confidence,
            'effective_from' => $price->effective_from?->toDateString(),
        ];
    }

    public function record(
        DefaultPrice $price,
        string $action,
        ?User $actor = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null,
    ): DefaultPriceHistory {
        return DefaultPriceHistory::create([
            'default_price_id' => $price->id,
            'country' => $price->country,
            'item' => $price->item,
            'region' => $price->region,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_by' => $actor?->id,
            'note' => $note,
        ]);
    }

    private function markActive(DefaultPrice $price, User $actor): void
    {
        $price->status = PriceStatus::Active;
        $price->submitted_by = null;
        $price->updated_by = $actor->id;

        if ($price->effective_from === null) {
            $price->effective_from = now()->toDateString();
        }

        $price->save();
    }
}
