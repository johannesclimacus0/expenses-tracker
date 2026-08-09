<?php

declare(strict_types=1);

namespace Actions;

use App\Actions\RecurringTransactions\CreateRecurringTransaction;
use App\Actions\RecurringTransactions\DeleteRecurringTransaction;
use App\Actions\RecurringTransactions\UpdateRecurringTransaction;
use App\DTOs\RecurringTransactions\RecurringTransactionData;
use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RecurringTransactionActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_create_action_calculates_the_first_upcoming_run(): void
    {
        CarbonImmutable::setTestNow('2026-03-01 10:00:00');
        $user = User::factory()->create();

        $recurringTransaction = app(CreateRecurringTransaction::class)->handle(
            $user,
            $this->data(startsAt: '2026-01-31 09:00:00'),
        );

        $this->assertTrue($recurringTransaction->user->is($user));
        $this->assertSame('2026-03-31 09:00:00', $recurringTransaction->next_run_at->toDateTimeString());
    }

    public function test_update_action_preserves_the_next_run_when_schedule_did_not_change(): void
    {
        CarbonImmutable::setTestNow('2026-03-01 10:00:00');
        $recurringTransaction = RecurringTransaction::factory()->create([
            'period' => RecurringPeriod::Monthly,
            'starts_at' => '2026-01-31 09:00:00',
            'next_run_at' => '2026-02-28 09:00:00',
        ]);

        $updated = app(UpdateRecurringTransaction::class)->handle(
            $recurringTransaction,
            $this->data(startsAt: '2026-01-31 09:00:00', description: 'Новое описание'),
        );

        $this->assertSame('Новое описание', $updated->description);
        $this->assertSame('2026-02-28 09:00:00', $updated->next_run_at->toDateTimeString());
    }

    public function test_update_action_recalculates_the_next_run_when_schedule_changes(): void
    {
        CarbonImmutable::setTestNow('2026-03-01 10:00:00');
        $recurringTransaction = RecurringTransaction::factory()->create([
            'period' => RecurringPeriod::Monthly,
            'starts_at' => '2026-01-31 09:00:00',
            'next_run_at' => '2026-03-31 09:00:00',
        ]);

        $updated = app(UpdateRecurringTransaction::class)->handle(
            $recurringTransaction,
            $this->data(
                startsAt: '2026-02-01 09:00:00',
                period: RecurringPeriod::Weekly,
            ),
        );

        $this->assertSame('2026-03-08 09:00:00', $updated->next_run_at->toDateTimeString());
    }

    public function test_update_action_recalculates_a_stale_date_when_reactivated(): void
    {
        CarbonImmutable::setTestNow('2026-03-01 10:00:00');
        $recurringTransaction = RecurringTransaction::factory()->create([
            'period' => RecurringPeriod::Monthly,
            'starts_at' => '2026-01-31 09:00:00',
            'next_run_at' => '2026-02-28 09:00:00',
            'is_active' => false,
        ]);

        $updated = app(UpdateRecurringTransaction::class)->handle(
            $recurringTransaction,
            $this->data(startsAt: '2026-01-31 09:00:00'),
        );

        $this->assertTrue($updated->is_active);
        $this->assertSame('2026-03-31 09:00:00', $updated->next_run_at->toDateTimeString());
    }

    public function test_delete_action_deletes_the_recurring_transaction(): void
    {
        $recurringTransaction = RecurringTransaction::factory()->create();

        app(DeleteRecurringTransaction::class)->handle($recurringTransaction);

        $this->assertModelMissing($recurringTransaction);
    }

    private function data(
        string $startsAt,
        RecurringPeriod $period = RecurringPeriod::Monthly,
        ?string $description = null,
    ): RecurringTransactionData {
        return new RecurringTransactionData(
            type: TransactionType::Expense,
            amount: '100.00',
            categoryId: null,
            description: $description,
            period: $period,
            startsAt: CarbonImmutable::parse($startsAt),
            isActive: true,
        );
    }
}
