<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255',
                Rule::unique(User::class, 'email')->ignore($this->user()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите имя',
            'name.max' => 'Имя не должно быть длиннее 255 символов',

            'email.required' => 'Введите почту',
            'email.email' => 'Введите корректную почту.',
            'email.unique' => 'Эта почта уже используется.',
        ];
    }
}
