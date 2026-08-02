<?php

namespace App\Services\Budgets;

use App\DTOs\Budgets\BudgetUsageData;
use App\Enums\TransactionType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class BudgetUsageService
{
    public function forMonth(User $user, CarbonImmutable $month): Collection
    {
        $month = $month->startOfMonth();

        $budgets = $user->budgets()
            ->with('category')
            ->whereDate('month', $month->toDateString())
            ->orderByRaw('category_id IS NULL DESC')
            ->orderBy('category_id')
            ->get();

        if ($budgets->isEmpty()) {
            return collect();
        }

        $expenseQuery = $user->transactions()
            ->where('type', TransactionType::Expense->value)
            ->whereBetween('occurred_at', [
                $month->startOfDay(),
                $month->endOfMonth()->endOfDay(),
            ]);

        $overallSpent = (string) (clone $expenseQuery)->sum('amount');
        $categoryTotals = (clone $expenseQuery)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');
        $warningPercent = $user->settings->budget_warning_percent;

        return $budgets->map(function ($budget) use ($overallSpent, $categoryTotals, $warningPercent): BudgetUsageData {
            $spent = $budget->isOverall()
                ? $overallSpent
                : (string) $categoryTotals->get($budget->category_id, '0.00');

            $budgetCents = $this->toCents($budget->amount);
            $spentCents = $this->toCents($spent);
            $remainingCents = $budgetCents - $spentCents;
            $percentage = $budgetCents > 0
                ? (int) round(($spentCents / $budgetCents) * 100)
                : 0;
            $exceeded = $remainingCents < 0;

            return new BudgetUsageData(
                budget: $budget,
                spent: $this->fromCents($spentCents),
                remaining: $this->fromCents($remainingCents),
                percentage: $percentage,
                warning: ! $exceeded && $percentage >= $warningPercent,
                exceeded: $exceeded,
            );
        });
    }

    private function toCents(int|float|string $amount): int
    {
        $value = trim((string) $amount);
        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return $negative ? -$cents : $cents;
    }

    private function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $sign.intdiv($absolute, 100).'.'.str_pad(
            (string) ($absolute % 100),
            2,
            '0',
            STR_PAD_LEFT,
        );
    }
}
