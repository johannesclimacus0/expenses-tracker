<?php

declare(strict_types=1);

namespace App\Services\RecurringTransactions;

use App\Enums\RecurringPeriod;
use Carbon\CarbonImmutable;

final class RecurringScheduleService
{
    public function nextRunAt(CarbonImmutable $startsAt, RecurringPeriod $period, CarbonImmutable $reference): CarbonImmutable
    {
        $occurrence = 0;
        $candidate = $startsAt;

        while ($candidate->lessThan($reference)) {
            $occurrence++;

            $candidate = match ($period) {
                RecurringPeriod::Weekly => $startsAt->addWeeks($occurrence),
                RecurringPeriod::Biweekly => $startsAt->addWeeks($occurrence * 2),
                RecurringPeriod::Monthly => $startsAt->addMonthsNoOverflow($occurrence),
                RecurringPeriod::Yearly => $startsAt->addYearsNoOverflow($occurrence),
            };
        }

        return $candidate;
    }
}
