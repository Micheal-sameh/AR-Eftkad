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
        // Only the membership code, date, visit type and a father or servant are required.
        return [
            'membership_code' => 'required|string|max:50',
            'date' => 'required|date_format:d-m-Y',
            'type' => 'required|integer|in:'.implode(',', array_column(EftkadType::all(), 'value')),
            // At least one of father / servant is required; the other is optional.
            'father_membership_code' => ['required_without:servant_membership_code', 'nullable', 'string', Rule::exists('users', 'membership_code')->where('type', UserType::FATHER)],
            'servant_membership_code' => 'required_without:father_membership_code|nullable|string|exists:users,membership_code',

            'correspondence_address' => 'nullable|string|max:2000',
            'mass_attendence' => 'nullable|integer|in:'.implode(',', array_column(MassAttendanceType::all(), 'value')),
            'needs' => 'nullable|array|max:20',
            'needs.*' => 'integer|in:'.implode(',', array_column(NeedType::all(), 'value')),
            'communication_means' => 'nullable|array|max:20',
            'communication_means.*' => 'integer|in:'.implode(',', array_column(CommunicationType::all(), 'value')),
            'attend_meetings' => 'nullable|integer|in:'.implode(',', array_column(BoolType::all(), 'value')),
            'need_eftkad_from_meeting' => 'nullable|string|max:255',
            'father_confession' => 'nullable|string|max:255',
            'mother_confession' => 'nullable|string|max:255',
            'children_confession' => 'nullable|string|max:255',
            'father_confessor' => 'nullable|string|max:255',
            'mother_confessor' => 'nullable|string|max:255',
            'children_confessor' => 'nullable|string|max:255',
            'need_eftkad_by_father' => 'nullable|integer|in:'.implode(',', array_column(BoolType::all(), 'value')),
            'location' => 'nullable|string|max:2000',
            'location_url' => 'nullable|string|max:2000|url:http,https',
            'general_notes' => 'nullable|string|max:5000',
        ];
    }
}
