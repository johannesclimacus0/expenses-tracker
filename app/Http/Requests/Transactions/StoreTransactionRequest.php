<?php

namespace App\Http\Requests\Transactions;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Transaction::class) ?? false;
    }

    public function rules(): array
    {
        $account = app(ResolveCurrentAccount::class)->handle($this->user());

        return [
            'type' => ['required', Rule::enum(TransactionType::class)],
            'amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'category_id' => ['nullable', 'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query
                        ->where('account_id', $account->getKey())
                        ->where('type', $this->input('type')),
                ),
            ],
            'description' => 'nullable|string|max:255',
            'occurred_at' => 'required|date',
        ];
    }

    protected function prepareForValidation(): void
    {
        $description = $this->input('description');

        $this->merge([
            'category_id' => $this->filled('category_id') ? $this->input('category_id') : null,
            'description' => is_string($description) && trim($description) !== '' ? trim($description) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Выберите тип транзакции',
            'type.enum' => 'Выбран неверный тип транзакции',

            'amount.required' => 'Введите сумму',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.decimal' => 'Укажите не более двух знаков после запятой',
            'amount.min' => 'Сумма должна быть не меньше 0,01.',
            'amount.max' => 'Сумма слишком большая',

            'category_id.integer' => 'Выбрана неверная категория',
            'category_id.exists' => 'Категория не найдена или не подходит для выбранного типа',

            'description.string' => 'Описание должно быть текстом',
            'description.max' => 'Описание не должно превышать 255 символов',

            'occurred_at.required' => 'Укажите дату и время транзакции',
            'occurred_at.date' => 'Укажите корректные дату и время',
        ];
    }
}
