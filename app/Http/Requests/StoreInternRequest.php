<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInternRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'npk'             => ['required', 'string', 'unique:interns,npk'],
            'name'            => ['required', 'string', 'max:255'],
            'gender'          => ['required', 'in:male,female'],
            'department_id'   => ['nullable', 'exists:departments,id'],
            'section_id'      => ['nullable', 'exists:sections,id'],
            'role_level'      => ['nullable', 'string', 'max:255'],
            'jabatan'         => ['nullable', 'string', 'max:255'],
            'area_id'         => ['nullable', 'exists:areas,id'],
            'line_id'         => ['nullable', 'exists:lines,id'],
            'station_id'      => ['nullable', 'exists:stations,id'],
            'join_date'       => ['required', 'date', 'before_or_equal:start_contract'],
            'start_contract'  => ['required', 'date'],
            'end_contract'    => ['required', 'date', 'after:start_contract'],
        ];
    }

    public function messages(): array
    {
        return [
            'npk.required'               => 'NPK is required',
            'npk.unique'                 => 'NPK is already registered',
            'name.required'              => 'Name is required',
            'gender.required'            => 'Gender is required',
            'gender.in'                  => 'Gender must be male or female',

            'join_date.required'         => 'Join date is required',
            'join_date.date'             => 'Invalid date format',
            'join_date.before_or_equal'  => 'Join date cannot be after the internship start date',

            'start_contract.required'    => 'Internship start date is required',
            'start_contract.date'        => 'Invalid date format',
            'end_contract.required'      => 'Internship end date is required',
            'end_contract.after'         => 'Internship end date must be after the start date',
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