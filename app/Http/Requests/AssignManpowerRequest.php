<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssignManpowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah dicek roleLevel di controller
    }

    public function rules(): array
    {
        return [
            'candidates'                        => ['required', 'array', 'min:1'],
            'candidates.*.npk'                   => [
                'required', 'string', 'max:255',
                'distinct', // npk tidak boleh sama antar baris dalam satu submission
                'unique:employees,npk',
                'unique:interns,npk',
            ],
            'candidates.*.name'                  => ['required', 'string', 'max:255'],
            'candidates.*.start_contract'        => ['required', 'date'],
            'candidates.*.end_contract'          => ['nullable', 'date', 'after:candidates.*.start_contract'],
        ];
    }

    public function messages(): array
    {
        return [
            'candidates.required'         => 'At least one candidate is required.',
            'candidates.*.npk.required'   => 'NPK is required for every candidate.',
            'candidates.*.npk.distinct'   => 'Each candidate in this submission must have a different NPK.',
            'candidates.*.npk.unique'     => 'This NPK is already registered.',
            'candidates.*.name.required'  => 'Name is required for every candidate.',
        ];
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