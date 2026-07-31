<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id ?? $this->route('employee');

        return [
            'npk'             => ['sometimes', 'string', "unique:employees,npk,{$employeeId}"],
            'name'            => ['sometimes', 'string', 'max:255'],
            'gender'          => ['sometimes', 'in:male,female'],
            'group'           => ['sometimes', 'in:A,B'],
            'department_id'   => ['nullable', 'exists:departments,id'],
            'section_id'      => ['nullable', 'exists:sections,id'],
            'role_level'      => ['nullable', 'string', 'max:255'],
            'jabatan'         => ['nullable', 'string', 'max:255'],
            'area_id'         => ['nullable', 'exists:areas,id'],
            'line_id'         => ['nullable', 'exists:lines,id'],
            'station_id'      => ['nullable', 'exists:stations,id'],
            'employment_type' => ['sometimes', 'in:permanent,contract,apprentice'],
            'pkwt_number'     => ['nullable', 'string', 'max:50'],
            'join_date'       => ['sometimes', 'date', 'before_or_equal:start_contract'],
            'start_contract'  => ['sometimes', 'date'],
            'end_contract'    => [
                'nullable',
                'date',
                'after:start_contract',
                'required_if:employment_type,contract',
                'required_if:employment_type,apprentice',
            ],
            'is_active'           => ['sometimes', 'boolean'],
            'deactivated_reason'  => [
                'nullable',
                'string',
                'max:255',
                'required_if:is_active,false',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'npk.unique'                => 'NPK is already registered',
            'gender.in'                 => 'Gender must be male or female',
            'employment_type.in'        => 'Employee type is invalid',

            'join_date.date'            => 'Invalid date format',
            'join_date.before_or_equal' => 'Join date cannot be after the contract start date',

            'end_contract.required_if'  => 'Contract end date is required for contract/apprentice type',
            'end_contract.after'        => 'Contract end date must be after the start date',

            'deactivated_reason.required_if' => 'Deactivation reason is required when marking as non-active',
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