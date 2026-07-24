<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'npk'             => ['required', 'string', 'unique:employees,npk'],
            'name'            => ['required', 'string', 'max:255'],
            'gender'          => ['required', 'in:male,female'],
            'group'          => ['required', 'in:A,B'],
            'department_id'   => ['nullable', 'exists:departments,id'],
            'section_id'      => ['nullable', 'exists:sections,id'],
            'role_level'      => ['nullable', 'string', 'max:255'],
            'jabatan'         => ['nullable', 'string', 'max:255'],
            'area_id'         => ['nullable', 'exists:areas,id'],
            'line_id'         => ['nullable', 'exists:lines,id'],
            'station_id'      => ['nullable', 'exists:stations,id'],
            'employment_type' => ['required', 'in:permanent,contract,apprentice'],
            'join_date'       => ['required', 'date', 'before_or_equal:start_contract'],
            'start_contract'  => ['required', 'date'],
            'end_contract'    => [
                'nullable',
                'date',
                'after:start_contract',
                // Required if not permanent
                'required_if:employment_type,contract',
                'required_if:employment_type,apprentice',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'npk.required'              => 'NPK is required',
            'npk.unique'                => 'NPK is already registered',
            'name.required'             => 'Name is required',
            'gender.required'           => 'Gender is required',
            'gender.in'                 => 'Gender must be male or female',
            'employment_type.required'  => 'Employee type is required',
            'employment_type.in'        => 'Employee type is invalid',

            'join_date.required'        => 'Join date is required',
            'join_date.date'            => 'Invalid date format',
            'join_date.before_or_equal' => 'Join date cannot be after the contract start date',

            'start_contract.required'   => 'Contract start date is required',
            'start_contract.date'       => 'Invalid date format',
            'end_contract.required_if'  => 'Contract end date is required for contract/apprentice type',
            'end_contract.after'        => 'Contract end date must be after the start date',
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