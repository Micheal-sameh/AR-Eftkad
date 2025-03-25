<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'membership_code' => 'required|string',
            'password' => 'required|string|min:6',
        ];
    }
}
