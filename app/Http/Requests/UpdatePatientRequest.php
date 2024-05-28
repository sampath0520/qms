<?php

namespace App\Http\Requests;

class UpdatePatientRequest extends BaseRequest
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

            'id' => 'required|exists:users,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $this->id,
            'phone_number' => 'required|numeric|unique:users,mobile_number,' . $this->id,
            'additional_info' => 'nullable|string',
        ];
    }
}
