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

    if (!$user) {
        return false;
    }

    $roleName = $user->roleLevel?->name;

    // Admin tidak punya batasan department
    if ($roleName === 'Admin') {
        return true;
    }

    // User dari department yang sama dengan FPTK ini boleh mengisi area/line.
    return $user->department
        && $user->department->name === $this->requisition()->department;
}

    public function rules(): array
{
    $lineRule = $this->requisition()->requiresLine()
        ? 'required|string|max:255'
        : 'nullable|string|max:255';

    return [
        'area_id'    => 'required|exists:areas,id',
        'line_id'    => 'nullable|exists:lines,id',
        'station_id' => 'nullable|exists:stations,id',
    ];
}

    public function messages(): array
    {
        return [
            'area.required' => 'Area is required.',
            'line.required' => 'Line is required because this FPTK department is Manufacturing.',
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Only users from the same department as this FPTK can fill in the area/line.',
        ], 403));
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