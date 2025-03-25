<?php

namespace App\Repository;

use App\Enums\BoolType;
use App\Enums\CommunicationType;
use App\Enums\MassAttendanceType;
use App\Enums\NeedType;

class SettingRepository
{
    public function enums()
    {
        $enums['user_type'] = BoolType::all();
        $enums['user_status'] = CommunicationType::all();
        $enums['visits_status'] = MassAttendanceType::all();
        $enums['visits_status'] = NeedType::all();

        return $enums;
    }
}
