<?php

namespace App\Actions\Budgets;

use App\DTOs\Budgets\BudgetData;
use App\Models\Budget;

final class UpdateBudget
{
    public function handle(Budget $budget, BudgetData $data): Budget
    {
        $budget->update([
            'amount' => $data->amount,
            'month' => $data->month,
            'category_id' => $data->categoryId,
        ]);

        return $budget->refresh();
    }
}
