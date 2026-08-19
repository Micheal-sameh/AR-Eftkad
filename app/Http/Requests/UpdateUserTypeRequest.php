<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|integer|in:'.implode(',', array_column(UserType::all(), 'value')),
        ];
    }
}
