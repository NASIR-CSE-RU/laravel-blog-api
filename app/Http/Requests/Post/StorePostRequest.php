<?php

namespace App\Http\Requests\Post;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'visibility' => ['required', 'string', Rule::in(['public', 'private'])],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException($this->validationErrorResponse($validator->errors()));
    }
}
