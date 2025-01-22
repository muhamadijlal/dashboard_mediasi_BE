<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ruas_id' => 'required|string',
            'gerbang_id' => 'required|string',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }

    public function messages()
    {
        return [
            'ruas_id.required'   => 'Ruas ID is required.',
            'ruas_id.string'     => 'Ruas ID must be a string.',
            'gerbang_id.required' => 'Gerbang ID is required.',
            'gerbang_id.string'   => 'Gerbang ID must be a string.',
            'start_date.required' => 'Start date is required.',
            'start_date.date'     => 'Start date must be a valid date.',
            'start_date.date_format' => 'Start date format must be Y-m-d.',
            'end_date.required'   => 'End date is required.',
            'end_date.date'       => 'End date must be a valid date.',
            'end_date.date_format' => 'End date format must be Y-m-d.',
            'end_date.after_or_equal' => 'End date must be greater than or equal to start date.',
        ];
    }
}
