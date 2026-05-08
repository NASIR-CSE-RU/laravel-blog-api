<?php

namespace App\Http\Requests\Reaction;

use App\Models\Reaction;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreReactionRequest extends FormRequest
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
            'reactable_type' => ['required', 'string', Rule::in(['post', 'comment'])],
            'reactable_id' => ['required', 'integer'],
            'type' => ['required', 'integer', Rule::in(Reaction::TYPES)],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException($this->validationErrorResponse($validator->errors()));
    }
}
