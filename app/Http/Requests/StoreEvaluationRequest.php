<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
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

            // Scores (dikirim sekaligus saat create, supaya create + scores
            // + recommendation jadi satu transaction yang atomic di controller)
            'scores' => 'nullable|array',
            'scores.*.criteria_id' => 'required_with:scores|integer|exists:evaluation_criteria,id',
            'scores.*.score' => 'required_with:scores|numeric',

            // Recommendation (opsional saat create)
            'recommendation' => 'nullable|array',
            'recommendation.employee_status' => 'nullable|in:permanen,kontrak_berakhir,perpanjang_kontrak',
            'recommendation.extend_pkwt' => 'nullable|boolean',
            'recommendation.pkwt_number' => 'nullable|string|max:10',
            'recommendation.extend_months' => 'nullable|integer|min:0',
            'recommendation.notes' => 'nullable|string',
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