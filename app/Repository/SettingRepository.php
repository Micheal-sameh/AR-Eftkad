<?php

namespace App\Repository;

use App\Enums\BoolType;
use App\Enums\CommunicationType;
use App\Enums\EftkadType;
use App\Enums\MassAttendanceType;
use App\Enums\NeedType;
use App\Enums\UserRole;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SettingRepository
{
    public function enums()
    {
        $enums['bool_type'] = BoolType::all();
        $enums['user_status'] = CommunicationType::all();
        $enums['mass_attendence_type'] = MassAttendanceType::all();
        $enums['visits_status'] = NeedType::all();
        $enums['user_type'] = UserType::all();
        $enums['eftkad_type'] = EftkadType::all();
        $enums['user_role'] = UserRole::all();

        return $enums;
    }

    public function filters()
    {
        return Cache::remember(User::FILTERS_CACHE_KEY, 600, function () {
            $byType = fn (int $type) => User::where('type', $type)
                ->get(['name', 'membership_code'])
                ->map->only(['name', 'membership_code'])
                ->values()
                ->all();

            return [
                'fathers' => $byType(UserType::FATHER),
                'servants' => $byType(UserType::SERVANT),
            ];
        });
    }
}
