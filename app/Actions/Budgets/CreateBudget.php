<?php

namespace App\Actions\Budgets;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\DTOs\Budgets\BudgetData;
use App\Models\Budget;
use App\Models\User;

final readonly class CreateBudget
{
    public function __construct(private ResolveCurrentAccount $accounts) {}

    public function handle(User $user, BudgetData $data): Budget
    {
        $account = $this->accounts->handle($user);

        return $user->budgets()->create([
            'account_id' => $account->getKey(),
            'amount' => $data->amount,
            'month' => $data->month,
            'category_id' => $data->categoryId,
        ]);
    }
}
