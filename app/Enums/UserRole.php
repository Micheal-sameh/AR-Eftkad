<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class UserRole
{
    public const OWNER = 'owner';

    public const SUPERADMIN = 'superadmin';

    public const ADMIN = 'admin';

    public const USER = 'user';

    /** Roles that may open the users page and see every visit. */
    public const MANAGERS = [self::ADMIN, self::SUPERADMIN, self::OWNER];

    /** Higher outranks lower; nobody manages a user of equal or higher rank (except the owner). */
    private const RANKS = [
        self::USER => 0,
        self::ADMIN => 1,
        self::SUPERADMIN => 2,
        self::OWNER => 3,
    ];

    public static function rank(?string $role): int
    {
        return self::RANKS[$role] ?? 0;
    }

    /** Highest rank among a set of role names. */
    public static function highestRank(iterable $roles): int
    {
        $rank = 0;
        foreach ($roles as $role) {
            $rank = max($rank, self::rank($role));
        }

        return $rank;
    }

    private static function localizedNames(): array
    {
        return [
            self::OWNER => [
                'en' => 'Owner',
                'ar' => 'المالك',
            ],
            self::SUPERADMIN => [
                'en' => 'Super Admin',
                'ar' => 'مدير عام',
            ],
            self::ADMIN => [
                'en' => 'Admin',
                'ar' => 'مدير',
            ],
            self::USER => [
                'en' => 'User',
                'ar' => 'مستخدم',
            ],
        ];
    }

    public static function all(): array
    {
        $locale = App::currentLocale();
        $names = self::localizedNames();

        return array_map(
            fn ($value) => [
                'name' => $names[$value][$locale],
                'value' => $value,
            ],
            array_keys($names)
        );
    }

    public static function getStringValue(string $value): string
    {
        $locale = App::currentLocale();
        $names = self::localizedNames();

        return $names[$value][$locale] ?? '';
    }

    public static function isValid(string $value): bool
    {
        return array_key_exists($value, self::localizedNames());
    }
}
