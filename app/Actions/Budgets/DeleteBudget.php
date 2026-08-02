<?php

namespace App\Actions\Budgets;

use App\Models\Budget;

final class DeleteBudget
{
    public function handle(Budget $budget): void
    {
        $budget->delete();
    }
}
