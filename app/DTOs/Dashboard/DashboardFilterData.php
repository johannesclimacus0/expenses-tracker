<?php

namespace App\DTOs\Dashboard;

use App\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;

final readonly class DashboardFilterData
{
    public function __construct(
        public DashboardPeriod $period,
        public CarbonImmutable $month,
        public ?CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {}

    public static function fromArray(array $data, DashboardPeriod $defaultPeriod): self
    {
        $period = isset($data['period'])
            ? DashboardPeriod::from($data['period'])
            : $defaultPeriod;
        $month = isset($data['month'])
            ? CarbonImmutable::parse($data['month'].'-01')->startOfMonth()
            : now()->startOfMonth();

        $start = match ($period) {
            DashboardPeriod::Month => $month,
            DashboardPeriod::Quarter => $month->subMonths(2)->startOfMonth(),
            DashboardPeriod::Year => $month->subMonths(11)->startOfMonth(),
            DashboardPeriod::All => null,
        };

        return new self(
            period: $period,
            month: $month,
            start: $start,
            end: $month->endOfMonth()->endOfDay(),
        );
    }
}
