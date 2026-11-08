<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Console\Commands\Dev;

use App\Models\User;
use Illuminate\Console\Command;
use Random\RandomException;

class RegenerateAdminApiKey extends Command
{
    protected $signature = 'admin:regenerate-api-key
                            {--username=akilimo : Username of the admin account}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Revoke the existing wildcard API key for the admin user and issue a new one';

    /**
     * @throws RandomException
     */
    public function handle(): int
    {
        $username = $this->option('username');

        $user = User::where('username', $username)->first();

        if ($user === null) {
            $this->error("No user found with username [{$username}].");

            return self::FAILURE;
        }

        $existing = $user->apiKeys()->where('is_active', true)->get();

        if ($existing->isNotEmpty() && !$this->option('force')) {
            $this->table(
                ['ID', 'Name', 'Prefix', 'Created'],
                $existing->map(fn ($k) => [$k->id, $k->name, $k->key_prefix, $k->created_at]),
            );

            if (!$this->confirm("Revoke the above key(s) for [{$username}] and generate a new one?")) {
                $this->line('Aborted.');

                return self::SUCCESS;
            }
        }

        // Revoke all active keys for this user
        $user->apiKeys()->where('is_active', true)->update(['is_active' => false]);

        $rawKey = 'ak_' . bin2hex(random_bytes(16));
        $prefix = substr($rawKey, 0, 12);

        $user->apiKeys()->create([
            'name' => 'Admin wildcard key',
            'key_prefix' => $prefix,
            'key_hash' => hash('sha256', $rawKey),
            'abilities' => null,
            'expires_at' => null,
        ]);

        $keyLine = "  X-Api-Key: {$rawKey}  ";
        $pad = str_repeat('─', mb_strlen($keyLine));

        $this->line('');
        $this->warn("┌{$pad}┐");
        $this->warn('│  COPY THIS KEY — it will NOT be shown again.  │');
        $this->warn("│{$keyLine}│");
        $this->warn("└{$pad}┘");
        $this->line('');

        return self::SUCCESS;
    }
}
