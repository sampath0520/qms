<?php

namespace App\Http\Requests;

class AddDoctorRequest extends BaseRequest
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
            'phone_number' => 'required|unique:doctors,mobile_number',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'speciality_arears' => 'required|array',
            'email' => 'required|email|unique:doctors,email',
            // 'clinic' => 'required|array',
            'additional_info' => 'nullable|string',
        ];
    }
}
