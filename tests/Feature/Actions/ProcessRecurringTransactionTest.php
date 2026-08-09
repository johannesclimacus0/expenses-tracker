<?php

declare(strict_types=1);

namespace Actions;

use App\Actions\RecurringTransactions\ProcessRecurringTransaction;
use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProcessRecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_transaction_and_advances_the_schedule(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create([
            'type' => TransactionType::Expense,
        ]);
        $recurringTransaction = RecurringTransaction::factory()->for($user)->create([
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => '1490.50',
            'description' => 'Подписка',
            'period' => RecurringPeriod::Monthly,
            'starts_at' => '2026-01-31 09:00:00',
            'next_run_at' => '2026-02-28 09:00:00',
            'last_run_at' => null,
            'is_active' => true,
        ]);

        $transaction = app(ProcessRecurringTransaction::class)->handle(
            $recurringTransaction,
            CarbonImmutable::parse('2026-02-28 09:00:00'),
        );

        $this->assertNotNull($transaction);
        $this->assertSame($user->id, $transaction->user_id);
        $this->assertSame($recurringTransaction->id, $transaction->recurring_transaction_id);
        $this->assertSame($category->id, $transaction->category_id);
        $this->assertSame(TransactionType::Expense, $transaction->type);
        $this->assertSame('1490.50', $transaction->amount);
        $this->assertSame('Подписка', $transaction->description);
        $this->assertSame('2026-02-28 09:00:00', $transaction->occurred_at->toDateTimeString());
        $this->assertSame('2026-02-28 09:00:00', $transaction->scheduled_for->toDateTimeString());

        $recurringTransaction->refresh();

        $this->assertSame('2026-02-28 09:00:00', $recurringTransaction->last_run_at->toDateTimeString());
        $this->assertSame('2026-03-31 09:00:00', $recurringTransaction->next_run_at->toDateTimeString());
    }

    public function test_it_does_nothing_when_the_schedule_is_inactive(): void
    {
        $recurringTransaction = RecurringTransaction::factory()->create([
            'next_run_at' => '2026-02-01 09:00:00',
            'is_active' => false,
        ]);

        $transaction = app(ProcessRecurringTransaction::class)->handle(
            $recurringTransaction,
            CarbonImmutable::parse('2026-02-02 09:00:00'),
        );

        $this->assertNull($transaction);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertNull($recurringTransaction->refresh()->last_run_at);
    }

    public function test_it_does_nothing_before_the_next_run_date(): void
    {
        $recurringTransaction = RecurringTransaction::factory()->create([
            'next_run_at' => '2026-02-02 09:00:00',
            'is_active' => true,
        ]);

        $transaction = app(ProcessRecurringTransaction::class)->handle(
            $recurringTransaction,
            CarbonImmutable::parse('2026-02-02 08:59:59'),
        );

        $this->assertNull($transaction);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertNull($recurringTransaction->refresh()->last_run_at);
    }

    public function test_the_same_occurrence_is_not_created_twice(): void
    {
        $recurringTransaction = RecurringTransaction::factory()->create([
            'period' => RecurringPeriod::Weekly,
            'starts_at' => '2026-02-01 09:00:00',
            'next_run_at' => '2026-02-08 09:00:00',
            'is_active' => true,
        ]);
        $now = CarbonImmutable::parse('2026-02-08 09:00:00');
        $action = app(ProcessRecurringTransaction::class);

        $firstTransaction = $action->handle($recurringTransaction, $now);
        $secondTransaction = $action->handle($recurringTransaction, $now);

        $this->assertNotNull($firstTransaction);
        $this->assertNull($secondTransaction);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertSame(
            '2026-02-15 09:00:00',
            $recurringTransaction->refresh()->next_run_at->toDateTimeString(),
        );
    }
}
