<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendPasswordResetEmailRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'email' => 'required|email|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => 'Введите почту',
            'email.email' => 'Введите валидную почту',
            'email.max' => 'Почта не должна превышать 255 символов'
        ];
    }
}
