<?php

declare(strict_types=1);

namespace App\Http\Requests\Goals;

use App\Enums\GoalStatus;
use App\Models\Goal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $goal = $this->route('goal');

        return $goal instanceof Goal
            && ($this->user()?->can('update', $goal) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(GoalStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'deadline' => $this->filled('deadline') ? $this->input('deadline') : null,
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
            'status.required' => 'Выберите статус цели',
            'status.enum' => 'Выбран неверный статус цели',
        ];
    }
}
