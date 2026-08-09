<?php

namespace Tests\Unit\Models;

use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_recurring_transaction_belongs_to_user_and_optional_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $recurringTransaction = RecurringTransaction::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $recurringTransaction->user());
        $this->assertInstanceOf(BelongsTo::class, $recurringTransaction->category());
        $this->assertTrue($recurringTransaction->user->is($user));
        $this->assertTrue($recurringTransaction->category->is($category));

        $withoutCategory = RecurringTransaction::factory()->for($user)->create([
            'category_id' => null,
        ]);

        $this->assertNull($withoutCategory->category);
    }

    public function test_recurring_transaction_has_generated_transactions(): void
    {
        $user = User::factory()->create();
        $recurringTransaction = RecurringTransaction::factory()->for($user)->create();
        $transaction = Transaction::factory()
            ->for($user)
            ->for($recurringTransaction, 'recurringTransaction')
            ->create([
                'scheduled_for' => '2026-08-10 09:00:00',
            ]);

        $this->assertInstanceOf(HasMany::class, $recurringTransaction->transactions());
        $this->assertTrue($recurringTransaction->transactions->contains($transaction));
        $this->assertTrue($transaction->recurringTransaction->is($recurringTransaction));
    }

    public function test_recurring_transaction_attributes_are_cast(): void
    {
        $recurringTransaction = RecurringTransaction::factory()->create([
            'type' => TransactionType::Income->value,
            'amount' => '100',
            'period' => RecurringPeriod::Biweekly->value,
            'starts_at' => '2026-08-10 09:00:00',
            'next_run_at' => '2026-08-24 09:00:00',
            'last_run_at' => '2026-07-27 09:00:00',
            'is_active' => 0,
        ])->fresh();

        $this->assertSame(TransactionType::Income, $recurringTransaction->type);
        $this->assertSame('100.00', $recurringTransaction->amount);
        $this->assertSame(RecurringPeriod::Biweekly, $recurringTransaction->period);
        $this->assertInstanceOf(CarbonImmutable::class, $recurringTransaction->starts_at);
        $this->assertInstanceOf(CarbonImmutable::class, $recurringTransaction->next_run_at);
        $this->assertInstanceOf(CarbonImmutable::class, $recurringTransaction->last_run_at);
        $this->assertFalse($recurringTransaction->is_active);
    }

    public function test_uuid_is_generated_and_used_as_route_key(): void
    {
        $recurringTransaction = RecurringTransaction::factory()->create();

        $this->assertNotNull($recurringTransaction->uuid);
        $this->assertSame('uuid', $recurringTransaction->getRouteKeyName());
        $this->assertSame($recurringTransaction->uuid, $recurringTransaction->getRouteKey());
    }

    public function test_deleting_recurring_transaction_preserves_generated_transactions(): void
    {
        $user = User::factory()->create();
        $recurringTransaction = RecurringTransaction::factory()->for($user)->create();
        $transaction = Transaction::factory()
            ->for($user)
            ->for($recurringTransaction, 'recurringTransaction')
            ->create([
                'scheduled_for' => '2026-08-10 09:00:00',
            ]);

        $recurringTransaction->delete();

        $this->assertModelMissing($recurringTransaction);
        $this->assertModelExists($transaction);
        $this->assertNull($transaction->refresh()->recurring_transaction_id);
        $this->assertSame('2026-08-10 09:00:00', $transaction->scheduled_for->format('Y-m-d H:i:s'));
    }
}
