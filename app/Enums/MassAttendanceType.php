<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class MassAttendanceType
{
    public const UNKNOWN = 1;
    public const REGULAR = 2;
    public const IRREGULAR = 3;

    private static function localizedNames(): array
    {
        return [
            self::UNKNOWN => [
                'en' => 'Unknown',
                'ar' => 'غير معروف',
            ],
            self::REGULAR => [
                'en' => 'Regular',
                'ar' => 'منتظم',
            ],
            self::IRREGULAR => [
                'en' => 'Irregular',
                'ar' => 'غير منتظم',
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
