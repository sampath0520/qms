<?php

namespace App\Http\Requests;

class AddClinicRequest extends BaseRequest
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
            'name' => 'required|string|unique:clinics,name',
            'address_1' => 'required|string',
            'address_2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'lat' => 'required|string',
            'long' => 'required|string',
            'email' => 'required|email',
            'contact_number' => 'required|string|max:15',
            'fax' => 'string',
            'additional_info' => 'string',
        ];
    }
}
