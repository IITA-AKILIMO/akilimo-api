<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands;

use App\Auth\TokenAbility;
use App\Enums\EnumUserRole as UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

use Random\RandomException;
use Throwable;

class GenerateApiKey extends Command
{
    protected $signature = 'api-key:generate
                            {--user= : ID, username or email of the key owner}
                            {--name= : Label for the key}
                            {--ability=* : Ability to grant (repeatable); "*" for wildcard. Default: the owner\'s role preset}
                            {--expires= : Expiry date (YYYY-MM-DD); omit for no expiry}';

    protected $description = 'Create an API key for a user and print it once (asks for anything not given as an option)';

    /** Up to this many users are offered as a list; beyond it the owner is searched for */
    private const PICK_LIST_LIMIT = 50;

    /**
     * @throws RandomException
     */
    public function handle(): int
    {
        $user = $this->resolveUser();
        if ($user === null) {
            return self::FAILURE;
        }

        $name = $this->option('name') ?: ($this->input->isInteractive()
            ? text('Key name', placeholder: 'e.g. Recommendations service', default: 'CLI key ' . now()->toDateString(), required: true, validate: fn ($v) => mb_strlen($v) > 100 ? 'At most 100 characters.' : null)
            : 'CLI key ' . now()->toDateString());

        $abilities = $this->resolveAbilities($user);
        if ($abilities === false) {
            return self::FAILURE;
        }

        $expiresAt = $this->resolveExpiry();
        if ($expiresAt === false) {
            return self::FAILURE;
        }

        $rawKey = 'ak_' . bin2hex(random_bytes(16));

        $apiKey = $user->apiKeys()->create([
            'name' => $name,
            'key_prefix' => substr($rawKey, 0, 12),
            'key_hash' => hash('sha256', $rawKey),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        $this->components->twoColumnDetail('Owner', "{$user->name} <{$user->email}>");
        $this->components->twoColumnDetail('Name', $apiKey->name);
        $this->components->twoColumnDetail('Prefix', $apiKey->key_prefix);
        $this->components->twoColumnDetail('Abilities', $abilities === null ? '* (wildcard)' : implode(', ', $abilities));
        $this->components->twoColumnDetail('Expires', $expiresAt?->toDateString() ?? 'never');

        $keyLine = "  {$rawKey}  ";
        $pad = str_repeat('─', mb_strlen($keyLine));

        $this->line('');
        $this->warn("┌{$pad}┐");
        $this->warn('│' . str_pad('  COPY THIS KEY — it will NOT be shown again.', mb_strlen($keyLine) + 2) . '│');
        $this->warn("│{$keyLine}│");
        $this->warn("└{$pad}┘");
        $this->line('Send it as "X-Api-Key: <key>" or "Authorization: Bearer <key>".');
        $this->line('');

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $given = $this->option('user');

        if ($given !== null) {
            $user = User::where('username', $given)
                ->orWhere('email', $given)
                ->when(ctype_digit((string)$given), fn ($q) => $q->orWhere('id', (int)$given))
                ->first();

            if ($user === null) {
                $this->components->error("No user found with ID, username or email [{$given}].");
            }

            return $user;
        }

        if (!$this->input->isInteractive()) {
            $this->components->error('Pass --user when running without interaction.');

            return null;
        }

        $label = fn (User $u) => "{$u->name} <{$u->email}> — " . ($u->role ?? UserRole::User)->value;

        // A short list is picked from directly (on Windows the prompt falls back to a numbered choice);
        // a long one is searched first
        $id = User::count() <= self::PICK_LIST_LIMIT
            ? select(
                label: 'Key owner',
                options: User::orderBy('name')->get()->mapWithKeys(fn (User $u) => [$u->id => $label($u)])->all(),
                scroll: 15,
            )
            : search(
                label: 'Key owner',
                options: fn (string $query) => User::query()
                    ->when($query !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")))
                    ->orderBy('name')
                    ->limit(20)
                    ->get()
                    ->mapWithKeys(fn (User $u) => [$u->id => $label($u)])
                    ->all(),
                placeholder: 'Type a name, username or email',
                scroll: 10,
            );

        return User::find($id);
    }

    /**
     * @return list<string>|null|false null = wildcard, false = invalid input
     */
    private function resolveAbilities(User $user): array|null|false
    {
        $preset = $this->rolePreset($user);
        $given = $this->option('ability');

        if ($given === [] && $this->input->isInteractive()) {
            $choice = select(
                label: 'Abilities',
                options: [
                    'preset' => 'Role preset (' . ($preset === null ? '* wildcard' : implode(', ', $preset)) . ')',
                    'custom' => 'Pick abilities',
                    'wildcard' => '* Wildcard (everything)',
                ],
                default: 'preset',
            );

            return match ($choice) {
                'preset' => $preset,
                'wildcard' => null,
                'custom' => multiselect(
                    label: 'Abilities to grant' . (windows_os() ? ' (comma-separated numbers)' : ''),
                    options: collect(TokenAbility::ALL)->mapWithKeys(fn ($a) => [$a => $a . (isset(TokenAbility::LABELS[$a]) ? ' — ' . TokenAbility::LABELS[$a] : '')])->all(),
                    default: $preset ?? [],
                    scroll: 15,
                    required: true,
                ),
            };
        }

        if ($given === []) {
            return $preset;
        }

        if (in_array(TokenAbility::WILDCARD, $given, true)) {
            return null;
        }

        $unknown = array_diff($given, TokenAbility::ALL);
        if ($unknown !== []) {
            $this->components->error('Unknown abilities: ' . implode(', ', $unknown) . '. Valid: ' . implode(', ', TokenAbility::ALL));

            return false;
        }

        return array_values(array_unique($given));
    }

    /** Same role presets as the admin UI; null = wildcard. */
    private function rolePreset(User $user): ?array
    {
        return match ($user->role ?? UserRole::User) {
            UserRole::Admin => null,
            UserRole::Partner => TokenAbility::PARTNER_ABILITIES,
            default => TokenAbility::PLAYGROUND_ABILITIES,
        };
    }

    private function resolveExpiry(): Carbon|null|false
    {
        $given = $this->option('expires');

        if ($given === null && $this->input->isInteractive()) {
            $given = text(
                label: 'Expiry date (optional)',
                placeholder: 'YYYY-MM-DD, empty for never',
                validate: fn ($v) => $v === '' || $this->parseFutureDate($v) !== null ? null : 'Enter a future date as YYYY-MM-DD.',
            );
        }

        if ($given === null || $given === '') {
            return null;
        }

        $date = $this->parseFutureDate($given);
        if ($date === null) {
            $this->components->error("Expiry [{$given}] must be a future date (YYYY-MM-DD).");

            return false;
        }

        return $date;
    }

    private function parseFutureDate(string $value): ?Carbon
    {
        try {
            $date = Carbon::parse($value)->endOfDay();
        } catch (Throwable) {
            return null;
        }

        return $date->isFuture() ? $date : null;
    }
}
