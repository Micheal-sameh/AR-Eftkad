<?php

namespace App\Http\Requests;

use App\Enums\BoolType;
use App\Enums\CommunicationType;
use App\Enums\EftkadType;
use App\Enums\MassAttendanceType;
use App\Enums\NeedType;
use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EftkadCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'membership_code' => 'required|string|max:50',
            'date' => 'required|date_format:d-m-Y',
            'correspondence_address' => 'string|max:2000',
            'mass_attendence' => 'required|integer|in:'.implode(',', array_column(MassAttendanceType::all(), 'value')),
            'needs' => 'array|min:1|max:20',
            'needs.*' => 'integer|in:'.implode(',', array_column(NeedType::all(), 'value')),
            'communication_means' => 'array|min:1|max:20',
            'communication_means.*' => 'integer|in:'.implode(',', array_column(CommunicationType::all(), 'value')),
            'attend_meetings' => 'integer|in:'.implode(',', array_column(BoolType::all(), 'value')),
            'need_eftkad_from_meeting' => 'string|max:255',
            'father_confession' => 'string|max:255',
            'mother_confession' => 'string|max:255',
            'children_confession' => 'string|max:255',
            'father_confessor' => 'nullable|string|max:255',
            'mother_confessor' => 'nullable|string|max:255',
            'children_confessor' => 'nullable|string|max:255',
            'need_eftkad_by_father' => 'integer|in:'.implode(',', array_column(BoolType::all(), 'value')),
            'location' => 'string|max:2000',
            'location_url' => 'nullable|string|max:2000|url:http,https',
            'general_notes' => 'string|max:5000',
            'father_membership_code' => ['required', 'string', Rule::exists('users', 'membership_code')->where('type', UserType::FATHER)],
            'servant_membership_code' => 'required|string|exists:users,membership_code',
            'type' => 'required|integer|in:'.implode(',', array_column(EftkadType::all(), 'value')),
        ];
    }
}
