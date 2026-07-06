<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateInternRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    $internId = $this->route('intern')?->id ?? $this->route('intern');

    return [
        'npk'            => ['sometimes', 'string', "unique:interns,npk,{$internId}"],
        'name'           => ['sometimes', 'string', 'max:255'],
        'gender'         => ['sometimes', 'in:male,female'],
        'department_id'  => ['nullable', 'exists:departments,id'],
        'section_id'     => ['nullable', 'exists:sections,id'],
        'role_level'     => ['nullable', 'string', 'max:255'],
        'jabatan'        => ['nullable', 'string', 'max:255'],
        'area_id'        => ['nullable', 'exists:areas,id'],
        'line_id'        => ['nullable', 'exists:lines,id'],
        'station_id'     => ['nullable', 'exists:stations,id'],
        'start_contract' => ['sometimes', 'date'],
        'end_contract'   => ['sometimes', 'date', 'after:start_contract'],
    ];
}

    public function messages(): array
    {
        return [
            'npk.unique'          => 'NPK is already registered',
            'gender.in'           => 'Gender must be male or female',
            'end_contract.after'  => 'Internship end date must be after the start date',
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