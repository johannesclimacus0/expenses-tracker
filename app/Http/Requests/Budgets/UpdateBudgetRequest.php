<?php

namespace App\Http\Requests\Budgets;

use App\Models\Budget;

class UpdateBudgetRequest extends StoreBudgetRequest
{
    public function authorize(): bool
    {
        $budget = $this->route('budget');

        return $budget instanceof Budget
            && ($this->user()?->can('update', $budget) ?? false);
    }
}
