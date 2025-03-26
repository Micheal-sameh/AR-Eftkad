<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class EftkadType
{
    public const CALL = 1;

    public const HOME = 2;

    private static function localizedNames(): array
    {
        return [
            self::CALL => [
                'en' => 'Call',
                'ar' => 'مكالمة',
            ],
            self::HOME => [
                'en' => 'Home',
                'ar' => 'منزل',
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

    public static function getStringValue(int $value): string
    {
        $locale = app()->getLocale();
        $names = self::localizedNames();

        return $names[$value][$locale] ?? '';
    }

    public static function isValid(int $value): bool
    {
        return array_key_exists($value, self::localizedNames());
    }
}
