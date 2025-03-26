<?php

namespace App\Repository;

use App\Enums\BoolType;
use App\Enums\CommunicationType;
use App\Enums\EftkadType;
use App\Enums\MassAttendanceType;
use App\Enums\NeedType;
use App\Enums\UserType;
use App\Models\User;

class SettingRepository
{
    public function enums()
    {
        $enums['bool_type'] = BoolType::all();
        $enums['user_status'] = CommunicationType::all();
        $enums['visits_status'] = MassAttendanceType::all();
        $enums['visits_status'] = NeedType::all();
        $enums['user_type'] = UserType::all();
        $enums['eftkad_type'] = EftkadType::all();

        return $enums;
    }

    public function filters()
    {
        $filters['fathers'] = User::where('type', UserType::FATHER)->get()->map->only(['name', 'membership_code']);
        $filters['servants'] = User::where('type', UserType::SERVANT)->get()->map->only(['name', 'membership_code']);

        return $filters;
    }
}
