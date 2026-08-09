<?php

namespace App\Http\Requests\RecurringTransactions;

use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Models\RecurringTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', RecurringTransaction::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(TransactionType::class)],
            'amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'category_id' => ['nullable', 'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()->getAuthIdentifier())
                        ->where('type', $this->input('type')),
                ),
            ],
            'description' => 'nullable|string|max:255',
            'period' => ['required', Rule::enum(RecurringPeriod::class)],
            'starts_at' => 'required|date',
            'is_active' => 'required|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $description = $this->input('description');
        $prepared = [
            'category_id' => $this->filled('category_id') ? $this->input('category_id') : null,
            'description' => is_string($description) && trim($description) !== ''
                ? trim($description)
                : null,
        ];

        if ($this->has('is_active')) {
            $prepared['is_active'] = $this->boolean('is_active');
        }

        $this->merge($prepared);
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Выберите тип транзакции',
            'type.enum' => 'Выбран неверный тип транзакции',

            'amount.required' => 'Введите сумму',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.decimal' => 'Укажите не более двух знаков после запятой',
            'amount.min' => 'Сумма должна быть не меньше 0,01',
            'amount.max' => 'Сумма слишком большая',

            'category_id.integer' => 'Выбрана неверная категория',
            'category_id.exists' => 'Категория не найдена или не подходит для выбранного типа',

            'description.string' => 'Описание должно быть текстом',
            'description.max' => 'Описание не должно превышать 255 символов',

            'period.required' => 'Выберите период повторения',
            'period.enum' => 'Выбран неверный период повторения',

            'starts_at.required' => 'Укажите дату первого выполнения',
            'starts_at.date' => 'Укажите корректную дату первого выполнения',

            'is_active.required' => 'Укажите состояние регулярной транзакции',
            'is_active.boolean' => 'Указано неверное состояние регулярной транзакции',
        ];
    }
}
