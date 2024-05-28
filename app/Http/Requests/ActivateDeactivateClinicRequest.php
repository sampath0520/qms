<?php

namespace App\Http\Requests;

class ActivateDeactivateClinicRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        return [
            'clinic_id' => 'required|integer|exists:clinics,id',
            'is_active' => 'required|integer|in:0,1',
        ];
    }
}
