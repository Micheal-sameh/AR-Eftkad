<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class UserRole
{
    public const SUPERADMIN = 'superadmin';

    public const ADMIN = 'admin';

    public const USER = 'user';

    private static function localizedNames(): array
    {
        return [
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
