<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role' => 'required|string|in:'.implode(',', array_column(UserRole::all(), 'value')),
        ];
    }
}
