<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EftkadShareRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'membership_codes' => 'required|array|min:1|max:20',
            // Visits are only shared with fathers and servants.
            'membership_codes.*' => ['required', 'string', Rule::exists('users', 'membership_code')->whereIn('type', [UserType::FATHER, UserType::SERVANT])],
        ];
    }
}
