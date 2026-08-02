<?php

namespace App\Http\Requests\Settings;

use App\Enums\Currency;
use App\Enums\DashboardPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
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
            'currency' => ['required', Rule::enum(Currency::class)],
            'dashboard_period' => ['required', Rule::enum(DashboardPeriod::class)],
            'transactions_per_page' => ['required', 'integer', Rule::in([10, 20, 50])],
            'budget_warning_percent' => 'required|integer|min:1|max:100',
            'show_cents' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'currency.required' => 'Выберите валюту',
            'currency.enum' => 'Выбрана неизвестная валюта',

            'dashboard_period.required' => 'Выберите период обзора',
            'dashboard_period.enum' => 'Выбран неизвестный период',

            'transactions_per_page.required' => 'Выберите количество транзакций',
            'transactions_per_page.in' => 'Доступно 10, 20 или 50 транзакций',

            'budget_warning_percent.required' => 'Укажите процент предупреждения',
            'budget_warning_percent.min' => 'Процент должен быть не меньше 1',
            'budget_warning_percent.max' => 'Процент должен быть не больше 100',

            'show_cents.required' => 'Не удалось определить настройку копеек',
            'show_cents.boolean' => 'Некорректное значение настройки копеек',
        ];
    }
}
