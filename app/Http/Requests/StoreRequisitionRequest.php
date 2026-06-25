<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'requester_name' => 'required|string|max:255',
            'request_date' => 'required|date',
            'group' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            'cost_employee' => 'nullable|string|max:255',
            'fulfilment_time' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'max_age' => 'nullable|integer|min:18',
            'min_experience' => 'nullable|integer|min:0',
            'technical_skill' => 'nullable|string',
            'soft_skill' => 'nullable|string',
            'description' => 'nullable|string',
            'cost_center' => 'nullable|string|max:255',
            'objective' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
            'employee_out' => 'nullable|string|max:255',
            'manpower_plan' => 'nullable|string',
            'unplanned_reason' => 'nullable|string',
            'manager' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'director' => 'nullable|string|max:255',
            'supervisor' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'requester_name.required' => 'The requester name is required.',
            'request_date.required' => 'The request date is required.',
            'request_date.date' => 'The request date must be a valid date.',
            'max_age.min' => 'The maximum age must be at least 18.',
            'min_experience.min' => 'The minimum experience must be at least 0.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
