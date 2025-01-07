<?php

namespace laravel\AutoApiScwv\FormRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class BaseFormRequest extends FormRequest
{
    /**
     * Override the failedValidation method to handle validation errors.
     *
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        // Check if a custom validation message is defined in the request
        $message = method_exists($this, 'validationMessage')
            ? $this->validationMessage()
            : 'Validation failed.';

        // Check if the request is an AJAX or API request
        if ($this->expectsJson() || request()->is('api/*') || request()->is('api')) {
            $response = response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $validator->errors(),
            ], 422);

            throw new ValidationException($validator, $response);
        }

        // Handle non-API requests: redirect with errors
        session()->flash('errors', $validator->errors());
        throw new ValidationException($validator);
    }

    /**
     * Default validation message (can be overridden in child classes).
     *
     * @return string
     */
    public function validationMessage()
    {
        return 'Validation failed.';
    }
}
