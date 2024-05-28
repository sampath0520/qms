<?php

namespace App\Http\Requests;

class UpdateScheduleRequest extends BaseRequest
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
            'schedule_id' => 'required|integer|exists:schedules,id',
            'doctor_id' => 'required|numeric|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i|before:end_time',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'maximum_slots' => 'required|numeric|min:1',
            'avg_time_per_person' => 'required|numeric|min:1',
        ];
    }
}
