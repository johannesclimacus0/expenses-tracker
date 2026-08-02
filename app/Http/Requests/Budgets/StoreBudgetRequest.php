<?php

namespace App\Http\Requests\Budgets;

use App\Enums\TransactionType;
use App\Models\Budget;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Budget::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|decimal:0,2|min:0.01|max:9999999999.99',
            'month' => 'required|date_format:Y-m',
            'category_id' => ['nullable', 'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query
                        ->where('user_id', $this->user()->getAuthIdentifier())
                        ->where('type', TransactionType::Expense->value),
                ),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['category_id' => $this->filled('category_id') ? $this->input('category_id') : null]);
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['month', 'category_id'])) {
                    return;
                }

                $month = CarbonImmutable::createFromFormat('Y-m', $this->input('month'))->startOfMonth();

                $query = $this->user()->budgets()->whereDate('month', $month->toDateString());

                if ($this->filled('category_id')) {
                    $query->where('category_id', $this->integer('category_id'));
                } else {
                    $query->whereNull('category_id');
                }

                $budget = $this->route('budget');

                if ($budget instanceof Budget) {
                    $query->whereKeyNot($budget->getKey());
                }

                if ($query->exists()) {
                    $validator->errors()->add(
                        'category_id',
                        'Такой бюджет на выбранный месяц уже существует',
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Введите сумму бюджета',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.decimal' => 'Укажите не более двух знаков после запятой',
            'amount.min' => 'Сумма должна быть не меньше 0,01',
            'amount.max' => 'Сумма слишком большая',

            'month.required' => 'Выберите месяц',
            'month.date_format' => 'Укажите корректный месяц',

            'category_id.integer' => 'Выбрана неверная категория',
            'category_id.exists' => 'Можно выбрать только свою категорию расходов',
        ];
    }
}
