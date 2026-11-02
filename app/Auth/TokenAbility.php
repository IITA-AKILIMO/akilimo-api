<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Auth;

final class TokenAbility
{
    // ── Broad abilities (backward-compatible) ─────────────────────────────────
    const WILDCARD = '*';

    const READ = 'read';

    const WRITE = 'write';

    const API_KEYS_MANAGE = 'api-keys:manage';

    const ADMIN = 'admin';

    // ── Granular abilities ────────────────────────────────────────────────────
    const RECOMMENDATIONS_COMPUTE = 'recommendations:compute';

    const RECOMMENDATIONS_READ = 'recommendations:read';

    const PRICES_WRITE = 'prices:write';

    const FEEDBACK_READ = 'feedback:read';

    const FEEDBACK_WRITE = 'feedback:write';

    const TRANSLATIONS_READ = 'translations:read';

    // ── AKILIMO MCP server (akilimo-mcp-agent) ────────────────────────────────
    /** A user's key for calling the AKILIMO MCP tools from their own MCP client. */
    const MCP_USE = 'mcp:use';

    /** The MCP server's own key, for checking users' MCP keys at POST /v1/mcp/verify-key. */
    const MCP_VERIFY_KEYS = 'mcp:verify-keys';

    // ── Role presets ──────────────────────────────────────────────────────────

    /** MCP keys users create for themselves: the MCP tools only. */
    const MCP_ABILITIES = [
        self::MCP_USE,
    ];

    /** Playground users: compute recommendations only. */
    const PLAYGROUND_ABILITIES = [
        self::RECOMMENDATIONS_COMPUTE,
    ];

    /** Partners: compute + read history, submit prices & feedback, read translations. */
    const PARTNER_ABILITIES = [
        self::RECOMMENDATIONS_COMPUTE,
        self::RECOMMENDATIONS_READ,
        self::PRICES_WRITE,
        self::FEEDBACK_WRITE,
        self::TRANSLATIONS_READ,
    ];

    /** All selectable abilities shown in the admin UI. */
    const ALL = [
        self::RECOMMENDATIONS_COMPUTE,
        self::RECOMMENDATIONS_READ,
        self::PRICES_WRITE,
        self::FEEDBACK_READ,
        self::FEEDBACK_WRITE,
        self::TRANSLATIONS_READ,
        self::MCP_USE,
        self::MCP_VERIFY_KEYS,
        self::API_KEYS_MANAGE,
        self::READ,
        self::WRITE,
        self::ADMIN,
    ];

    /**
     * Broad abilities that imply specific granular ones.
     * Used by ApiKey::can() for backward-compatible hierarchy checks.
     */
    const BROAD_GRANTS = [
        self::WRITE => [
            self::RECOMMENDATIONS_COMPUTE,
            self::PRICES_WRITE,
            self::FEEDBACK_WRITE,
            self::WRITE,
        ],
        self::READ => [
            self::RECOMMENDATIONS_READ,
            self::FEEDBACK_READ,
            self::TRANSLATIONS_READ,
            self::READ,
        ],
    ];

    /** Human-readable labels for the admin UI. */
    const LABELS = [
        self::RECOMMENDATIONS_COMPUTE => 'Compute recommendations',
        self::RECOMMENDATIONS_READ => 'Read recommendation history',
        self::PRICES_WRITE => 'Submit price updates',
        self::FEEDBACK_READ => 'Read user feedback',
        self::FEEDBACK_WRITE => 'Submit user feedback',
        self::TRANSLATIONS_READ => 'Read translations',
        self::MCP_USE => 'Use the AKILIMO MCP tools',
        self::MCP_VERIFY_KEYS => 'Verify MCP keys (MCP server only)',
        self::API_KEYS_MANAGE => 'Manage own API keys',
        self::READ => 'Broad read (legacy)',
        self::WRITE => 'Broad write (legacy)',
        self::ADMIN => 'Admin operations',
    ];

    /**
     * Resolve whether a set of granted abilities satisfies a requested one.
     * Single source of truth used by both ApiKey and PersonalAccessToken.
     */
    public static function check(array $granted, string $requested): bool
    {
        foreach ($granted as $ability) {
            if ($ability === self::WILDCARD || $ability === self::ADMIN) {
                return true;
            }

            if ($ability === $requested) {
                return true;
            }

            if (in_array($requested, self::BROAD_GRANTS[$ability] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    /** Grouped for the admin UI ability picker. */
    const GROUPS = [
        'Recommendations' => [
            self::RECOMMENDATIONS_COMPUTE,
            self::RECOMMENDATIONS_READ,
        ],
        'Prices' => [
            self::PRICES_WRITE,
        ],
        'Feedback' => [
            self::FEEDBACK_READ,
            self::FEEDBACK_WRITE,
        ],
        'Translations' => [
            self::TRANSLATIONS_READ,
        ],
        'MCP' => [
            self::MCP_USE,
            self::MCP_VERIFY_KEYS,
        ],
        'Keys' => [
            self::API_KEYS_MANAGE,
        ],
        'Legacy / Broad' => [
            self::READ,
            self::WRITE,
            self::ADMIN,
        ],
    ];
}
