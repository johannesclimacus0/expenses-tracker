<?php

declare(strict_types=1);

namespace App\Services\Goals;

use App\DTOs\Goals\GoalProgressData;
use App\Enums\GoalContributionType;
use App\Enums\GoalStatus;
use App\Models\Goal;
use Carbon\CarbonImmutable;

final class GoalProgressService
{
    public function for(Goal $goal): GoalProgressData
    {
        $deposited = $this->normalize((string) $goal->contributions()
            ->where('type', GoalContributionType::Deposit)
            ->sum('amount'));
        $withdrawn = $this->normalize((string) $goal->contributions()
            ->where('type', GoalContributionType::Withdrawal)
            ->sum('amount'));
        $current = bcsub($deposited, $withdrawn, 2);

        if (bccomp($current, '0.00', 2) < 0) {
            $current = '0.00';
        }

        $remaining = bcsub($goal->target_amount, $current, 2);

        if (bccomp($remaining, '0.00', 2) < 0) {
            $remaining = '0.00';
        }

        $targetCents = $this->toCents($goal->target_amount);
        $currentCents = $this->toCents($current);
        $percentage = $targetCents > 0
            ? (int) min(100, round(($currentCents / $targetCents) * 100))
            : 0;
        $isCompleted = bccomp($current, $goal->target_amount, 2) >= 0;
        $isOverdue = !$isCompleted
            && $goal->status === GoalStatus::Active
            && $goal->deadline !== null
            && $goal->deadline->isBefore(CarbonImmutable::today());

        return new GoalProgressData(
            deposited: $deposited,
            withdrawn: $withdrawn,
            currentAmount: $current,
            remainingAmount: $remaining,
            percentage: $percentage,
            isCompleted: $isCompleted,
            isOverdue: $isOverdue,
        );
    }

    private function normalize(string $amount): string
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return $whole . '.' . str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
