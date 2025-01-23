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
            'ruas_id.required'   => __('Ruas ID is required.'),
            'ruas_id.string'     => __('Ruas ID must be a string.'),
            'gerbang_id.required' => __('Gerbang ID is required.'),
            'gerbang_id.string'   => __('Gerbang ID must be a string.'),
            'start_date.required' => __('Start date is required.'),
            'start_date.date'     => __('Start date must be a valid date.'),
            'start_date.date_format' => __('Start date format must be Y-m-d.'),
            'end_date.required'   => __('End date is required.'),
            'end_date.date'       => __('End date must be a valid date.'),
            'end_date.date_format' => __('End date format must be Y-m-d.'),
            'end_date.after_or_equal' => __('End date must be greater than or equal to start date.'),
        ];
    }
}
