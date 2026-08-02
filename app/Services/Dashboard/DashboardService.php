<?php

namespace App\Services\Dashboard;

use App\DTOs\Dashboard\DashboardData;
use App\DTOs\Dashboard\DashboardFilterData;
use App\Enums\TransactionType;
use App\Models\User;
use App\Services\Budgets\BudgetUsageService;

final class DashboardService
{
    public function __construct(private BudgetUsageService $budgetUsageService) {}

    public function build(User $user, DashboardFilterData $filter): DashboardData
    {
        $transactions = $user->transactions();

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
