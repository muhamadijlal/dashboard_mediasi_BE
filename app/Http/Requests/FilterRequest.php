<?php

namespace App\Http\Requests;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

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
        $rules = [
            'ruas_id' => 'required|string',
            'gerbang_id' => 'required|string',
        ];

        if ($this->has('start_date') || $this->has('end_date')) {
            // Jika salah satu dari parameter optional ada, semua menjadi required
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'ruas_id.required' => 'Ruas ID is required.',
            'gerbang_id.required' => 'Gerbang ID is required.',
            'start_date.required' => 'Start date is required when any optional parameter is provided.',
            'end_date.required' => 'End date is required when any optional parameter is provided.',
            'start_date.date' => 'Start date must be a valid date.',
            'end_date.date' => 'End date must be a valid date.',
        ];
    }

    /**
     * Customize the response when validation fails.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return \Illuminate\Http\Response
    */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
