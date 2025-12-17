<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class BaseRequest extends FormRequest
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
            //
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $firstErrorField = $errors->keys()[0];
        $firstError = $errors->first($firstErrorField);

        throw new HttpResponseException(
            response()->json([
                'error' => $firstError,
            ], 422)
        );
    }

    protected function passedValidation()
    {
        $validatedData = $this->validated();
        $this->replace($validatedData);
    }

    public function getLocale(): string
    {
        return $this->header('Accept-Language', 'en');
    }
}