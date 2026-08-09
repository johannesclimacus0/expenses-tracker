<?php

declare(strict_types=1);

namespace App\Http\Requests\Goals;

use App\Enums\GoalContributionType;
use App\Models\Goal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreGoalContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $goal = $this->route('goal');

        return $goal instanceof Goal
            && ($this->user()?->can('contribute', $goal) ?? false);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(GoalContributionType::class)],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'contributed_at' => ['required', 'date', 'before_or_equal:now'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $note = $this->input('note');

        $this->merge([
            'note' => is_string($note) && trim($note) !== ''
                ? trim($note)
                : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Выберите тип операции',
            'type.enum' => 'Выбран неверный тип операции',
            'amount.required' => 'Введите сумму',
            'amount.numeric' => 'Сумма должна быть числом',
            'amount.decimal' => 'Укажите не более двух знаков после запятой',
            'amount.min' => 'Сумма должна быть не меньше 0,01',
            'amount.max' => 'Сумма слишком большая',
            'contributed_at.required' => 'Укажите дату операции',
            'contributed_at.date' => 'Укажите корректную дату операции',
            'contributed_at.before_or_equal' => 'Дата операции не может быть в будущем',
            'note.string' => 'Заметка должна быть текстом',
            'note.max' => 'Заметка не должна превышать 255 символов',
        ];
    }
}
