<?php

namespace App\Http\Requests\Accounts;

use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Account::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'currency' => ['required', Rule::enum(Currency::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите название счета',
            'name.max' => 'Название не должно превышать 100 символов',
            'currency.required' => 'Выберите валюту',
            'currency.enum' => 'Выбрана неверная валюта',
        ];
    }
}
