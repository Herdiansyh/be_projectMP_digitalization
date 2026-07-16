<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEvaluationRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_status' => 'nullable|in:permanen,kontrak_berakhir',
            'extend_pkwt' => 'nullable|boolean',
            'pkwt_number' => 'nullable|string|max:10',
            'extend_months' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
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
