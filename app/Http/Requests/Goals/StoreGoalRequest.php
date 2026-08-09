<?php

declare(strict_types=1);

namespace App\Http\Requests\Goals;

use App\Enums\GoalStatus;
use App\Models\Goal;
use Illuminate\Foundation\Http\FormRequest;

final class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Goal::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:' . GoalStatus::Active->value],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'deadline' => $this->filled('deadline') ? $this->input('deadline') : null,
            'status' => GoalStatus::Active->value,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите название цели',
            'name.string' => 'Название цели должно быть текстом',
            'name.max' => 'Название цели не должно превышать 255 символов',
            'target_amount.required' => 'Введите целевую сумму',
            'target_amount.numeric' => 'Целевая сумма должна быть числом',
            'target_amount.decimal' => 'Укажите не более двух знаков после запятой',
            'target_amount.min' => 'Целевая сумма должна быть не меньше 0,01',
            'target_amount.max' => 'Целевая сумма слишком большая',
            'deadline.date' => 'Укажите корректную дату',
            'deadline.after_or_equal' => 'Срок цели не может быть в прошлом',
        ];
    }
}
