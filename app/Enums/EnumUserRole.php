<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Enums;

use LaracraftTech\LaravelUsefulAdditions\Traits\UsefulEnums;

enum EnumUserRole: string
{
    use UsefulEnums;

    case Admin = 'admin';
    case Partner = 'partner';

    case User = 'user';

    /** Roles that may access the admin panel. */
    public static function adminRoles(): array
    {
        return [self::Admin, self::Partner];
    }

    /** Comma-separated role values for middleware route definitions. */
    public static function adminMiddlewareParam(): string
    {
        return implode(',', array_map(fn ($r) => $r->value, self::adminRoles()));
    }
}
