<?php

namespace App\Services\Dashboard;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\DTOs\Dashboard\DashboardData;
use App\DTOs\Dashboard\DashboardFilterData;
use App\Enums\TransactionType;
use App\Models\User;
use App\Services\Budgets\BudgetUsageService;

final class DashboardService
{
    public function __construct(
        private BudgetUsageService $budgetUsageService,
        private ResolveCurrentAccount $resolveCurrentAccount,
    ) {}

    public function build(User $user, DashboardFilterData $filter): DashboardData
    {
        $account = $this->resolveCurrentAccount->handle($user);
        $transactions = $user->transactions()->where('account_id', $account->id);

        if ($filter->start === null) {
            $transactions->where('occurred_at', '<=', $filter->end);
        } else {
            $transactions->whereBetween('occurred_at', [$filter->start, $filter->end]);
        }

        $income = (clone $transactions)
            ->where('type', TransactionType::Income)
            ->sum('amount');

        $expense = (clone $transactions)
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $balance = bcsub((string) $income, (string) $expense, 2);

        $latestTransactions = $user->transactions()
            ->where('account_id', $account->id)
            ->with('category')
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get();

        $budgetUsage = $this->budgetUsageService->forMonth(
            $user,
            $filter->month,
        );

        return new DashboardData(
            month: $filter->month,
            period: $filter->period,
            balance: $balance,
            income: (string) $income,
            expenses: (string) $expense,
            budgetUsage: $budgetUsage,
            latestTransactions: $latestTransactions,
        );
    }
}
