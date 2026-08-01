<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите имя',
            'name.max' => 'Имя не должно превышать 255 символов',
            'name.string' => 'Неверный формат имени',

            'email.email' => 'Неверный формат почты',
            'email.required' => 'Введите почту',
            'email.max' => 'Почта не должно превышать 255 символов',
            'email.unique' => 'Пользователь с такой почтой уже существует',

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
