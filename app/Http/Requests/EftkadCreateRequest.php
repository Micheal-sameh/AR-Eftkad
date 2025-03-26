<?php

namespace App\Http\Requests;

use App\Enums\BoolType;
use App\Enums\CommunicationType;
use App\Enums\MassAttendanceType;
use App\Enums\NeedType;
use Illuminate\Foundation\Http\FormRequest;

class EftkadCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'membership_code' => 'required',
            'date' => 'required|date_format:d-m-Y',
            'correspondence_address' => 'string',
            'mass_attendence' => 'required|integer|in:'.implode(',', array_column(MassAttendanceType::all(), 'value')),
            'needs' => 'array|min:1',
            'needs.*' => 'integer|in:'.implode(',', array_column(NeedType::all(), 'value')),
            'communication_means' => 'array|min:1',
            'communication_means.*' => 'integer|in:'.implode(',', array_column(CommunicationType::all(), 'value')),
            'attend_meetings' => 'integer|in:'.implode(',', array_column(BoolType::all(), 'value')),
            'need_eftkad_from_meeting' => 'string',
            'father_confession' => 'string',
            'mother_confession' => 'string',
            'children_confession' => 'string',
            'need_eftkad_by_father' => 'integer|in:'.implode(',', array_column(BoolType::all(), 'value')),
            'location' => 'string',
            'general_notes' => 'string',
            'father_membership_code' => 'required|string|exists:users,membership_code',
            'servant_membership_code' => 'required|string|exists:users,membership_code',
        ];
    }
}
