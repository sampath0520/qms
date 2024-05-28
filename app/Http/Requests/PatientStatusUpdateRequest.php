<?php

namespace App\Http\Requests;

class PatientStatusUpdateRequest extends BaseRequest
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
            'patient_booking_id' => 'required|integer|exists:patient_bookings,id',
            'status' => 'required|integer|in:1,2,3,4,5,6,7',
            'reason' => 'required_if:status,3',
        ];
    }
}
