<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class NeedType
{
    public const SENIORS = 1;
    public const PATIENT = 2;
    public const JOB = 3;
    public const STUDING_HELP = 4;
    public const TNAWEL = 5;
    public const EMERGENCY_CALL = 6;

    private static function localizedNames(): array
    {
        return [
            self::SENIORS => [
                'en' => 'Seniors',
                'ar' => 'مسنين',
            ],
            self::PATIENT => [
                'en' => 'Patent',
                'ar' => 'منتظم',
            ],
            self::JOB => [
                'en' => 'Job',
                'ar' => 'غير منتظم',
            ],
            self::STUDING_HELP => [
                'en' => 'Studing Help',
                'ar' => 'غير منتظم',
            ],
            self::TNAWEL => [
                'en' => 'Tnawel',
                'ar' => 'تناول في المنزل',
            ],
            self::EMERGENCY_CALL => [
                'en' => 'Emergency calls',
                'ar' => 'اتصال تليفوني دوري للطوارئ',
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
