<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PromoteInternRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Semua field di bawah OPSIONAL — kalau tidak dikirim, service
            // akan auto-fill dari data Intern existing (lihat InternPromotionService).
            // NPK sengaja TIDAK ada di sini: NPK employee baru selalu ikut NPK intern,
            // tidak bisa diubah lewat form promote.
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'jabatan' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|in:contract,permanent',
            'start_contract' => 'nullable|date',
            'end_contract' => 'nullable|date|after_or_equal:start_contract',
            'notes' => 'nullable|string',
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