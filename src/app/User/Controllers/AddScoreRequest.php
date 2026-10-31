<?php

namespace App\User\Controllers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class AddScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['userId' => $this->route('userId')]);
    }

    public function rules(): array
    {
        return [
            'userId' => ['required', 'uuid'],
            'total' => ['required_without:score', 'prohibits:score', 'integer'],
            'score' => ['required_without:total', 'prohibits:total', 'string', 'regex:/^[+\-]\d+$/'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status_code' => 422,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
