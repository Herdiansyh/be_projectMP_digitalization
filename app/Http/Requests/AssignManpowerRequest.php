<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AssignManpowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user && $user->roleLevel?->name === 'HR Admin';
    }

    public function rules(): array
    {
        return [
            'npk'            => 'required|string|max:255',
            'name'           => 'required|string|max:255',
            'start_contract' => 'required|date',
            'end_contract'   => 'nullable|date|after_or_equal:start_contract',
        ];
    }

    public function messages(): array
    {
        return [
            'npk.required'  => 'Candidate NPK is required.',
            'name.required' => 'Candidate name is required.',
            'start_contract.required' => 'Contract start date is required.',
            'end_contract.after_or_equal' => 'Contract end date must not be before the start date.',
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Only HR Admin can fill in manpower data.',
        ], 403));
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}