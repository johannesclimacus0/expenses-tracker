<?php

namespace App\Actions\Budgets;

use App\DTOs\Budgets\BudgetData;
use App\Models\Budget;
use App\Models\User;

final class CreateBudget
{
    public function handle(User $user, BudgetData $data): Budget
    {
        return $user->budgets()->create([
            'amount' => $data->amount,
            'month' => $data->month,
            'category_id' => $data->categoryId,
        ]);
    }
}
