<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Введите валидную почту',
            'email.required' => 'Введите почту',

            'password.confirmed' => 'Пароли не совпадают',
            'password.required' => 'Введите пароль',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.mixed' => 'Добавьте строчные и заглавные буквы',
            'password.numbers' => 'Добавьте хотя бы одну цифру',
            'password.symbols' => 'Добавьте хотя бы один специальный символ',
            'password.uncompromised' => 'Этот пароль найден в утечках данных',
        ];
    }
}
