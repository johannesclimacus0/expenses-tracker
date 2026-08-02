<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\DTOs\Settings\SettingsData;
use App\Models\User;
use App\Models\UserSetting;

final class UpdateSettings
{
    public function handle(User $user, SettingsData $data): UserSetting
    {
        /** @var UserSetting $settings */
        $settings = $user->settings()->updateOrCreate(
            [],
            [
                'currency' => $data->currency,
                'dashboard_period' => $data->dashboardPeriod,
                'transactions_per_page' => $data->transactionsPerPage,
                'budget_warning_percent' => $data->budgetWarningPercent,
                'show_cents' => $data->showCents,
            ],
        );

        return $settings;
    }
}
