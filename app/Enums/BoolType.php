<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class BoolType
{
    public const FALSE = 0;
    public const True = 1;

    private static function localizedNames(): array
    {
        return [
            self::True => [
                'en' => 'True',
                'ar' => 'نعم',
            ],
            self::FALSE => [
                'en' => 'False',
                'ar' => 'لا',
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
