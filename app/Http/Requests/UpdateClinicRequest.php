<?php

namespace App\Http\Requests;

class UpdateClinicRequest  extends BaseRequest
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
            'id' => 'required|exists:clinics,id',
            'name' => 'required|string|unique:clinics,name,' . $this->id,
            'address_1' => 'required|string',
            'address_2' => 'string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required',
            'lat' => 'required|string',
            'long' => 'required|string',
            'email' => 'required|email',
            'contact_number' => 'required|string|max:15',
            'fax' => 'string',
            'additional_info' => 'string',
        ];
    }
}
