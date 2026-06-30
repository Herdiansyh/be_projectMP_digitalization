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
            'department_id'   => ['nullable', 'exists:departments,id'],
            'section_id'      => ['nullable', 'exists:sections,id'],
            'role_level_id'   => ['nullable', 'exists:role_levels,id'],
            'jabatan'         => ['nullable', 'string', 'max:255'],
            'area'            => ['nullable', 'string', 'max:255'],
            'station'         => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'in:permanent,contract,apprentice'],
            'status'          => ['required', 'in:active,nonactive,resigned'],
            'start_contract'  => ['required', 'date'],
            'end_contract'    => [
                'nullable',
                'date',
                'after:start_contract',
                // Wajib jika bukan permanent
                'required_if:employment_type,contract',
                'required_if:employment_type,apprentice',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'npk.required'              => 'NPK wajib diisi',
            'npk.unique'                => 'NPK sudah terdaftar',
            'name.required'             => 'Nama wajib diisi',
            'gender.required'           => 'Gender wajib dipilih',
            'gender.in'                 => 'Gender harus male atau female',
            'employment_type.required'  => 'Tipe karyawan wajib dipilih',
            'employment_type.in'        => 'Tipe karyawan tidak valid',
            'status.required'           => 'Status wajib dipilih',
            'status.in'                 => 'Status tidak valid',
            'start_contract.required'   => 'Tanggal mulai kontrak wajib diisi',
            'start_contract.date'       => 'Format tanggal tidak valid',
            'end_contract.required_if'  => 'Tanggal akhir kontrak wajib diisi untuk tipe contract/apprentice',
            'end_contract.after'        => 'Tanggal akhir kontrak harus setelah tanggal mulai',
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