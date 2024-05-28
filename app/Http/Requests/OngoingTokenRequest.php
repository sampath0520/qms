<?php

namespace App\Http\Requests;

class OngoingTokenRequest extends BaseRequest
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
            'schedule_id' => 'required|exists:schedules,id',
            'booking_id' => 'required|exists:patient_bookings,id',
        ];
    }
}
