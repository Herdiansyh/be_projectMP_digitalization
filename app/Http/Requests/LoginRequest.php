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
        ];
    }

    public function messages(): array
    {
        return [
            'npk.required' => 'NPK wajib diisi',
            'npk.string'   => 'NPK harus berupa teks',
            'npk.exists'   => 'NPK tidak ditemukan',
            'password.required' => 'Password wajib diisi',
            'password.string'   => 'Password harus berupa teks',
            'password.min'      => 'Password minimal 8 karakter',
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