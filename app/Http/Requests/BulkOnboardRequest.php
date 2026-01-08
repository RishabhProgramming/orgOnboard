<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BulkOnboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organizations'                 => 'required|array|max:1000',
            'organizations.*.name'          => 'required|string',
            'organizations.*.domain'        => 'required|string',
            'organizations.*.contact_email' => 'nullable|email',
        ];
    }

    protected function failedValidation($validator)
    {
        throw new ValidationException(
            $validator,
            response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
