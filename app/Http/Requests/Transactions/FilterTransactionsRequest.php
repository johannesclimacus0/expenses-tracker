<?php

namespace App\Http\Requests\Transactions;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterTransactionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Transaction::class) ?? false;
    }

    public function rules(): array
    {
        $toRules = ['nullable', 'date'];

        if ($this->filled('from')) {
            $toRules[] = 'after_or_equal:from';
        }

        return [
            'type' => ['nullable', Rule::enum(TransactionType::class)],
            'category' => ['nullable', 'uuid',
                Rule::exists('categories', 'uuid')->where(
                    fn ($query) => $query->where(
                        'user_id',
                        $this->user()->getAuthIdentifier(),
                    ),
                ),
            ],
            'from' => 'nullable|date',
            'to' => $toRules,
        ];
    }

    public function messages(): array
    {
        return [
            'type.enum' => 'Выбран неверный тип транзакции',

            'category.uuid' => 'Выбрана неверная категория',
            'category.exists' => 'Категория не найдена',

            'from.date' => 'Укажите корректную начальную дату',

            'to.date' => 'Укажите корректную конечную дату',
            'to.after_or_equal' => 'Конечная дата не может быть раньше начальной',
        ];
    }
}
