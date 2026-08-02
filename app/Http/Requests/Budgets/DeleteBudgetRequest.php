<?php

namespace App\Http\Requests\Budgets;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;

class DeleteBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $budget = $this->route('budget');

        return $budget instanceof Budget
            && ($this->user()?->can('delete', $budget) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
