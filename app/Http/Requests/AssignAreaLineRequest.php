<?php

namespace App\Http\Requests;

use App\Models\Requisition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AssignAreaLineRequest extends FormRequest
{
    protected ?Requisition $requisition = null;

    protected function requisition(): Requisition
    {
        if (!$this->requisition) {
            $this->requisition = Requisition::findOrFail($this->route('noReq'));
        }
        return $this->requisition;
    }

    public function authorize(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $roleName = $user->roleLevel?->name;
        if ($roleName === 'Admin') return true;

        return $user->department
            && $user->department->name === $this->requisition()->department;
    }

    public function rules(): array
    {
        $requiresLine = $this->requisition()->requiresLine();

        return [
            'candidates'                    => ['required', 'array', 'min:1'],
            'candidates.*.npk'               => [
                'required', 'string',
                // Harus cocok dengan salah satu npk di pending_candidates
                function ($attribute, $value, $fail) {
                    $pending = collect($this->requisition()->pending_candidates ?? []);
                    if (!$pending->pluck('npk')->contains($value)) {
                        $fail("NPK {$value} was not found among the manpower data entered by HRD for this FPTK.");
                    }
                },
            ],
            'candidates.*.area_id'           => ['required', 'exists:areas,id'],
            'candidates.*.line_id'           => [$requiresLine ? 'required' : 'nullable', 'exists:lines,id'],
            'candidates.*.station_id'        => ['nullable', 'exists:stations,id'],
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