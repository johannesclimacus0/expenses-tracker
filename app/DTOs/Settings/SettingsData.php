<?php

namespace App\DTOs\Settings;

use App\Enums\Currency;
use App\Enums\DashboardPeriod;

final readonly class SettingsData
{
    public function __construct(
        public Currency $currency,
        public DashboardPeriod $dashboardPeriod,
        public int $transactionsPerPage,
        public int $budgetWarningPercent,
        public bool $showCents,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currency: Currency::from($data['currency']),
            dashboardPeriod: DashboardPeriod::from($data['dashboard_period']),
            transactionsPerPage: (int) $data['transactions_per_page'],
            budgetWarningPercent: (int) $data['budget_warning_percent'],
            showCents: (bool) $data['show_cents'],
        );
    }
}
