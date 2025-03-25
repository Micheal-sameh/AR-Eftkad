<?php

namespace App\Enums;

use Illuminate\Support\Facades\App;

class CommunicationType
{
    public const WHATSAPP_CMC = 1;
    public const FACEBOOK_GROUP = 2;
    public const KENISTY_APP = 3;
    public const NO_COMMUNICATION = 4;

    private static function localizedNames(): array
    {
        return [
            self::WHATSAPP_CMC => [
                'en' => 'Whatsapp CMC',
                'ar' => ' واتساب المركز الاعلامي',
            ],
            self::FACEBOOK_GROUP => [
                'en' => 'Facebook Group',
                'ar' => 'مجموعة الفيسبوك',
            ],
            self::KENISTY_APP => [
                'en' => 'Kenisty App',
                'ar' => ' تطبيق كينسيتي',
            ],
            self::NO_COMMUNICATION => [
                'en' => ' No Communication',
                'ar' => ' لا يوجد اتصال',
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
