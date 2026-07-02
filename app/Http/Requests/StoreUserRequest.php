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
            'can_view_manpower'     => 'boolean',
            'approver_manager_id'  => 'nullable|exists:users,id',
            'approver_division_id' => 'nullable|exists:users,id',
            'approver_director_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'npk.required'         => 'NPK is required.',
            'npk.unique'           => 'NPK is already registered.',
            'name.required'        => 'Name is required.',
            'username.required'    => 'Username is required.',
            'username.unique'      => 'Username is already taken.',
            'email.required'       => 'Email is required.',
            'email.email'          => 'Invalid email format.',
            'email.unique'         => 'Email is already registered.',
            'password.required'    => 'Password is required.',
            'password.min'         => 'Password must be at least 8 characters.',
            'password.confirmed'   => 'Password confirmation does not match.',
            'department_id.exists' => 'Department not found.',
            'section_id.exists'    => 'Section not found.',
            'role_level_id.exists' => 'Role level not found.',
            'director_id.exists'   => 'Director not found.',
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