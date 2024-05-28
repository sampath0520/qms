<?php

namespace App\Http\Requests;

class DateWiseScheduleRequest extends BaseRequest
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
            'doctor_id' => 'required|numeric|exists:schedules,doctor_id',
            'date' => 'required|date_format:Y-m-d',
        ];
    }
}
