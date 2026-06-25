<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by AdminMiddleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'npk'            => 'required|string|max:50|unique:users,npk',
            'name'           => 'required|string|max:255',
            'username'       => 'required|string|max:100|unique:users,username',
            'email'          => 'required|email|max:255|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'department_id'  => 'nullable|exists:departments,id',
            'section_id'     => 'nullable|exists:sections,id',
            'role_level_id'  => 'nullable|exists:role_levels,id',
            'director_id'    => 'nullable|exists:users,id',
            'is_admin'       => 'boolean',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'npk.required'         => 'NPK wajib diisi.',
            'npk.unique'           => 'NPK sudah terdaftar.',
            'name.required'        => 'Nama wajib diisi.',
            'username.required'    => 'Username wajib diisi.',
            'username.unique'      => 'Username sudah digunakan.',
            'email.required'       => 'Email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Email sudah terdaftar.',
            'password.required'    => 'Password wajib diisi.',
            'password.min'         => 'Password minimal 8 karakter.',
            'password.confirmed'   => 'Konfirmasi password tidak cocok.',
            'department_id.exists' => 'Department tidak ditemukan.',
            'section_id.exists'    => 'Section tidak ditemukan.',
            'role_level_id.exists' => 'Role level tidak ditemukan.',
            'director_id.exists'   => 'Director tidak ditemukan.',
        ];
    }

    /**
     * Handle failed validation — return JSON instead of redirect.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}