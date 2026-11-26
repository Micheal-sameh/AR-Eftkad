<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;

/**
 * An admin adds a father or servant before they ever sign in. The membership code is what
 * links the account to their SSO identity on first login (see AvarewaseUserProvisioner).
 */
class CreateUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->membership_code)) {
            $this->merge(['membership_code' => strtoupper(trim($this->membership_code))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'membership_code' => 'required|string|max:50|unique:users,membership_code',
            'type' => 'required|integer|in:'.implode(',', array_column(UserType::all(), 'value')),
            'phone' => 'nullable|string|max:30|unique:users,phone',
        ];
    }
}
