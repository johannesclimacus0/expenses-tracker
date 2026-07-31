<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
