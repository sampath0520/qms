<?php

namespace App\Http\Requests;

class AddRatingRequest extends BaseRequest
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
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|int',
            'type' => 'required|in:1,2',
            'description' => 'nullable',
            'relevent_id' => 'required'
        ];
    }
}
