<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
            'remember' => 'boolean|sometimes',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Введите адрес электронной почты.',
            'email.string' => 'Адрес электронной почты должен быть строкой.',
            'email.email' => 'Введите корректный адрес электронной почты.',
            'email.max' => 'Адрес электронной почты не должен превышать 255 символов.',

            'password.required' => 'Введите пароль.',
            'password.string' => 'Пароль должен быть строкой.',

            'remember.boolean' => 'Некорректное значение поля "Запомнить меня".',
        ];
    }
}
