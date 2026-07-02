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

        return $user && $user->name === $this->requisition()->requester_name;
    }

    public function rules(): array
    {
        $lineRule = $this->requisition()->requiresLine()
            ? 'required|string|max:255'
            : 'nullable|string|max:255';

        return [
            'area' => 'required|string|max:255',
            'line' => $lineRule,
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
            'message' => 'Only the requester who created this FPTK can fill in the area/line.',
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