<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RecurringPeriod;
use App\Models\RecurringTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProcessRecurringTransactionsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_command_processes_a_due_recurring_transaction(): void
    {
        CarbonImmutable::setTestNow('2026-08-03 10:00:00');
        $recurringTransaction = RecurringTransaction::factory()->create([
            'period' => RecurringPeriod::Monthly,
            'starts_at' => '2026-07-01 09:00:00',
            'next_run_at' => '2026-08-01 09:00:00',
            'last_run_at' => null,
            'is_active' => true,
        ]);

        $this->artisan('transactions:process-recurring')
            ->expectsOutput('Создано транзакций: 1')
            ->assertSuccessful();

        $transaction = $recurringTransaction->transactions()->sole();

        $this->assertSame('2026-08-01 09:00:00', $transaction->scheduled_for->toDateTimeString());
        $this->assertSame('2026-08-01 09:00:00', $transaction->occurred_at->toDateTimeString());
        $this->assertSame('2026-08-01 09:00:00', $recurringTransaction->refresh()->last_run_at->toDateTimeString());
        $this->assertSame('2026-09-01 09:00:00', $recurringTransaction->next_run_at->toDateTimeString());
    }

    public function test_command_ignores_future_and_inactive_schedules(): void
    {
        CarbonImmutable::setTestNow('2026-08-03 10:00:00');
        $future = RecurringTransaction::factory()->create([
            'next_run_at' => '2026-08-04 09:00:00',
            'is_active' => true,
        ]);
        $inactive = RecurringTransaction::factory()->create([
            'next_run_at' => '2026-08-01 09:00:00',
            'is_active' => false,
        ]);

        $this->artisan('transactions:process-recurring')
            ->expectsOutput('Создано транзакций: 0')
            ->assertSuccessful();

        $this->assertDatabaseCount('transactions', 0);
        $this->assertNull($future->refresh()->last_run_at);
        $this->assertNull($inactive->refresh()->last_run_at);
    }

    public function test_command_processes_multiple_missed_occurrences(): void
    {
        CarbonImmutable::setTestNow('2026-01-29 09:00:00');
        $recurringTransaction = RecurringTransaction::factory()->create([
            'period' => RecurringPeriod::Weekly,
            'starts_at' => '2026-01-01 09:00:00',
            'next_run_at' => '2026-01-08 09:00:00',
            'last_run_at' => null,
            'is_active' => true,
        ]);

        $this->artisan('transactions:process-recurring')
            ->expectsOutput('Создано транзакций: 4')
            ->assertSuccessful();

        $scheduledDates = $recurringTransaction->transactions()
            ->orderBy('scheduled_for')
            ->pluck('scheduled_for')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateTimeString())
            ->all();

        $this->assertSame([
            '2026-01-08 09:00:00',
            '2026-01-15 09:00:00',
            '2026-01-22 09:00:00',
            '2026-01-29 09:00:00',
        ], $scheduledDates);
        $this->assertSame(
            '2026-02-05 09:00:00',
            $recurringTransaction->refresh()->next_run_at->toDateTimeString(),
        );
    }

    public function test_running_command_twice_does_not_create_duplicates(): void
    {
        CarbonImmutable::setTestNow('2026-08-08 09:00:00');
        $recurringTransaction = RecurringTransaction::factory()->create([
            'period' => RecurringPeriod::Weekly,
            'starts_at' => '2026-08-01 09:00:00',
            'next_run_at' => '2026-08-08 09:00:00',
            'is_active' => true,
        ]);

        $this->artisan('transactions:process-recurring')
            ->expectsOutput('Создано транзакций: 1')
            ->assertSuccessful();

        $this->artisan('transactions:process-recurring')
            ->expectsOutput('Создано транзакций: 0')
            ->assertSuccessful();

        $this->assertCount(1, $recurringTransaction->transactions);
        $this->assertSame(
            '2026-08-15 09:00:00',
            $recurringTransaction->refresh()->next_run_at->toDateTimeString(),
        );
    }
}
