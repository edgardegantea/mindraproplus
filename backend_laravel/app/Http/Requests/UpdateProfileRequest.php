<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                      => 'sometimes|string|max:255',
            'current_password'          => 'required_with:new_password|string',
            'new_password'              => [
                'sometimes',
                'string',
                Password::min(8)->mixedCase()->numbers(),
                'confirmed',
                'different:current_password',
            ],
            'new_password_confirmation' => 'required_with:new_password|string',
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.different' => 'La nueva contraseña debe ser diferente a la actual.',
        ];
    }
}
