<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => 'nullable|exists:departments,id',
            'department_head_id' => 'nullable|exists:users,id',
            'section_head_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'npk' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'join_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'pkwt' => 'nullable|string|max:10',
            'reminder_date' => 'nullable|date',
            'reminder_note' => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
