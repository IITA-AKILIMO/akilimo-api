<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Service;

use App\Models\Currency;
use App\Models\FertilizerType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * The lists the admin price forms choose from, so currency, unit and item are
 * picked rather than typed: a typo in any of them quietly breaks a price list
 * (an item the lists do not know, a unit no list can show, a currency with no
 * rounding step).
 *
 * Everything comes from the database (currencies, price_units, price_items), and
 * the same tables are the validation rules, so the server refuses what the form
 * would not offer.
 */
class PriceLookupService
{
    /**
     * Everything a price form needs, as Inertia props.
     *
     * @return array{currencies: list<array{code: string, name: string}>, units: list<array{value: string, label: string}>,
     *               items: list<array{item: string, unit: string}>, currencyByCountry: array<string, string>}
     */
    public function forForms(): array
    {
        return [
            'currencies' => $this->currencies(),
            'units' => $this->units(),
            'items' => $this->items(),
            'currencyByCountry' => $this->currencyByCountry(),
            'fertilizerTypes' => $this->fertilizerTypes(),
        ];
    }

    /**
     * The fertilisers a per-bag price can be for. The label is how the catalogue
     * names it (urea), so choosing the item can pick the fertiliser.
     *
     * @return list<array{type: string, name: string, label: string|null}>
     */
    public function fertilizerTypes(): array
    {
        return FertilizerType::query()->orderBy('name')->get(['type', 'name', 'label'])
            ->map(fn (FertilizerType $t) => ['type' => $t->type, 'name' => $t->name, 'label' => $t->label])
            ->all();
    }

    /**
     * One entry per currency code, by code.
     *
     * @return list<array{code: string, name: string}>
     */
    public function currencies(): array
    {
        return Currency::query()
            ->whereNotNull('currency_code')
            ->orderBy('currency_code')
            ->get(['currency_code', 'currency_name'])
            ->unique('currency_code')
            ->map(fn (Currency $c) => ['code' => (string) $c->currency_code, 'name' => (string) $c->currency_name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function units(): array
    {
        return DB::table('price_units')->orderBy('sort_order')->orderBy('value')->get(['value', 'label'])
            ->map(fn ($u) => ['value' => (string) $u->value, 'label' => (string) $u->label])
            ->all();
    }

    /**
     * The items a price can be for, each with the unit it is priced in, so
     * choosing the item fills in the unit.
     *
     * @return list<array{item: string, unit: string}>
     */
    public function items(): array
    {
        return DB::table('price_items')->orderBy('item')->get(['item', 'unit'])
            ->sortBy('item', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn ($i) => ['item' => (string) $i->item, 'unit' => (string) $i->unit])
            ->values()
            ->all();
    }

    /**
     * The currency a country uses, so choosing the country fills in the currency.
     *
     * @return array<string, string>
     */
    public function currencyByCountry(): array
    {
        // A country names its own currency (countries.currency_code); older rows that only link a currency to a country still count
        $own = DB::table('countries')->whereNotNull('currency_code')->pluck('currency_code', 'code')->map(fn ($c) => (string) $c)->all();

        return $own + Currency::query()
            ->whereNotNull('country_code')
            ->whereNotNull('currency_code')
            ->orderBy('id')
            ->get(['country_code', 'currency_code'])
            ->unique('country_code')
            ->mapWithKeys(fn (Currency $c) => [(string) $c->country_code => (string) $c->currency_code])
            ->all();
    }

    /**
     * The item as spelled in price_items. The database matches names without
     * regard to case, so cassup would pass validation; saving it as typed would
     * leave a price whose name differs from the list's.
     */
    public function canonicalItem(string $item): string
    {
        return (string) (DB::table('price_items')->where('item', trim($item))->value('item') ?? $item);
    }

    public function unitRule(): Exists
    {
        return Rule::exists('price_units', 'value');
    }

    public function itemRule(): Exists
    {
        return Rule::exists('price_items', 'item');
    }

    public function currencyRule(): Exists
    {
        return Rule::exists('currencies', 'currency_code');
    }
}
