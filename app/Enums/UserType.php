<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class UserType
{
    public const FATHER = 1;

    public const SERVANT = 2;

    private static function localizedNames(): array
    {
        return [
            self::FATHER => [
                'en' => 'Father',
                'ar' => 'كاهن',
            ],
            self::SERVANT => [
                'en' => 'Servant',
                'ar' => 'خادم',
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
