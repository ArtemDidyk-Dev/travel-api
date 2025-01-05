<?php

namespace App\Http\Requests\Filters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator as ValidatorSecond;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class TourFilterRequests extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'price_to' => 'nullable|numeric',
            'price_from' => 'nullable|numeric',
            'sort_by' => Rule::in(['price', 'start_date', 'end_date']),
            'sort_order' => Rule::in(['asc', 'desc']),
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'sort_by' => $this->input('sort_by', 'price'),
            'sort_order' => $this->input('sort_order', 'asc'),
        ]);
    }

    public function failedValidation(Validator|ValidatorSecond $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 400));
    }

    public function messages(): array
    {
        return [
            'sort_by' => "The sort by field must be one of 'price', 'start_date', 'end_date'",
            'sort_order' => 'The sort order field must be one of asc, desc',
        ];
    }
}
