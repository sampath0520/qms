<?php

namespace App\Http\Requests;

class AddPatientRequest extends BaseRequest
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
            'phone_number' => 'required|string|min:7',
            'appoinment_for' => 'required|integer|in:1,2',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'dob' => 'required|date',
            'email' => 'required|email',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'schedule_id' => 'required|integer|exists:schedules,id',
            'symptoms' => 'required|string',
        ];
    }
}
