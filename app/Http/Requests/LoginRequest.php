<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'npk'      => ['required', 'string', 'exists:users,npk'],
            'password' => ['required', 'string', 'min:8'],
            'role_level_id' => ['required', 'integer', 'exists:role_levels,id'],

        ];
    }

    public function messages(): array
    {
        return [
            'npk.required' => 'NPK is required',
            'npk.string'   => 'NPK must be text',
            'npk.exists'   => 'NPK not found',
            'password.required' => 'Password is required',
            'password.string'   => 'Password must be text',
            'password.min'      => 'Password must be at least 8 characters',
            'role_level_id.required' => 'Role is required',
            'role_level_id.exists'   => 'Selected role is invalid',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}