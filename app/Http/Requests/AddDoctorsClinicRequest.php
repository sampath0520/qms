<?php

namespace App\Http\Requests;

class AddDoctorsClinicRequest extends BaseRequest
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
            'doctor_id' => 'required|numeric|exists:doctors,id|unique:doctors_clinics,doctor_id,NULL,id,clinic_id,' . $this->clinic_id,
            // 'clinic_id' => 'required|numeric|exists:clinics,id',
        ];
    }
}
