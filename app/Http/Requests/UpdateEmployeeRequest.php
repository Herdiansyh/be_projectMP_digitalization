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
            'department_id'   => ['nullable', 'exists:departments,id'],
            'section_id'      => ['nullable', 'exists:sections,id'],
            'role_level_id'   => ['nullable', 'exists:role_levels,id'],
            'jabatan'         => ['nullable', 'string', 'max:255'],
            'area'            => ['nullable', 'string', 'max:255'],
            'station'         => ['nullable', 'string', 'max:255'],
            'employment_type' => ['sometimes', 'in:permanent,contract,apprentice'],
            'status'          => ['sometimes', 'in:active,nonactive,resigned'],
            'start_contract'  => ['sometimes', 'date'],
            'end_contract'    => [
                'nullable',
                'date',
                'after:start_contract',
                'required_if:employment_type,contract',
                'required_if:employment_type,apprentice',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'npk.unique'               => 'NPK sudah terdaftar',
            'gender.in'                => 'Gender harus male atau female',
            'employment_type.in'       => 'Tipe karyawan tidak valid',
            'status.in'                => 'Status tidak valid',
            'end_contract.required_if' => 'Tanggal akhir kontrak wajib diisi untuk tipe contract/apprentice',
            'end_contract.after'       => 'Tanggal akhir kontrak harus setelah tanggal mulai',
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