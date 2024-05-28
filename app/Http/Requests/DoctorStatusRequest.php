<?php

namespace App\Http\Requests;

class DoctorStatusRequest extends BaseRequest
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
            'doctor_id' => 'required|integer|exists:doctors,id',
            'is_active' => 'required|integer|in:0,1',
        ];
    }
}
