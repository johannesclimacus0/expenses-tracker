<?php

namespace App\DTOs\Dashboard;

use App\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final readonly class DashboardData
{
    public function __construct(
        public CarbonImmutable $month,
        public DashboardPeriod $period,
        public string $balance,
        public string $income,
        public string $expenses,
        public Collection $budgetUsage,
        public EloquentCollection $latestTransactions,
    ) {}
}
