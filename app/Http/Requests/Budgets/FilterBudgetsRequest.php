<?php

namespace App\Http\Requests\Budgets;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;

class FilterBudgetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Budget::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'month' => 'nullable|date_format:Y-m',
        ];
    }

    public function messages(): array
    {
        return [
            'month.date_format' => 'Укажите корректный месяц',
        ];
    }
}
