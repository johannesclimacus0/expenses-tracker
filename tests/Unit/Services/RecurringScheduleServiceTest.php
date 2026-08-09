<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\RecurringPeriod;
use App\Services\RecurringTransactions\RecurringScheduleService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RecurringScheduleServiceTest extends TestCase
{
    private RecurringScheduleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RecurringScheduleService;
    }

    #[Test]
    public function it_returns_the_start_date_when_it_is_not_before_the_reference(): void
    {
        $result = $this->service->nextRunAt(
            CarbonImmutable::parse('2026-08-10 09:00:00'),
            RecurringPeriod::Monthly,
            CarbonImmutable::parse('2026-08-05 09:00:00'),
        );

        $this->assertSame('2026-08-10 09:00:00', $result->toDateTimeString());
    }

    #[Test]
    public function it_returns_the_reference_when_it_matches_an_occurrence(): void
    {
        $result = $this->service->nextRunAt(
            CarbonImmutable::parse('2026-08-01 09:00:00'),
            RecurringPeriod::Weekly,
            CarbonImmutable::parse('2026-08-08 09:00:00'),
        );

        $this->assertSame('2026-08-08 09:00:00', $result->toDateTimeString());
    }

    #[Test]
    public function it_calculates_a_weekly_occurrence(): void
    {
        $result = $this->service->nextRunAt(
            CarbonImmutable::parse('2026-08-01 09:00:00'),
            RecurringPeriod::Weekly,
            CarbonImmutable::parse('2026-08-03 09:00:00'),
        );

        $this->assertSame('2026-08-08 09:00:00', $result->toDateTimeString());
    }

    #[Test]
    public function it_calculates_a_biweekly_occurrence(): void
    {
        $result = $this->service->nextRunAt(
            CarbonImmutable::parse('2026-08-01 09:00:00'),
            RecurringPeriod::Biweekly,
            CarbonImmutable::parse('2026-08-03 09:00:00'),
        );

        $this->assertSame('2026-08-15 09:00:00', $result->toDateTimeString());
    }

    #[Test]
    public function it_uses_the_last_available_day_in_a_short_month(): void
    {
        $result = $this->service->nextRunAt(
            CarbonImmutable::parse('2026-01-31 09:00:00'),
            RecurringPeriod::Monthly,
            CarbonImmutable::parse('2026-02-01 09:00:00'),
        );

        $this->assertSame('2026-02-28 09:00:00', $result->toDateTimeString());
    }

    #[Test]
    public function a_short_month_does_not_shift_later_monthly_occurrences(): void
    {
        $result = $this->service->nextRunAt(
            CarbonImmutable::parse('2026-01-31 09:00:00'),
            RecurringPeriod::Monthly,
            CarbonImmutable::parse('2026-03-01 09:00:00'),
        );

        $this->assertSame('2026-03-31 09:00:00', $result->toDateTimeString());
    }

    #[Test]
    public function it_handles_a_yearly_occurrence_from_leap_day(): void
    {
        $result = $this->service->nextRunAt(
            CarbonImmutable::parse('2024-02-29 09:00:00'),
            RecurringPeriod::Yearly,
            CarbonImmutable::parse('2028-01-01 09:00:00'),
        );

        $this->assertSame('2028-02-29 09:00:00', $result->toDateTimeString());
    }
}
