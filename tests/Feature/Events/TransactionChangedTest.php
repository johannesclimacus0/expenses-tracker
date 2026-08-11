<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Enums\TransactionType;
use App\Events\TransactionDeleted;
use App\Events\TransactionUpdated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class TransactionChangedTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_transaction_dispatches_transaction_updated_event(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
            'type' => TransactionType::Expense,
        ]);

        Event::fake([TransactionUpdated::class]);

        $this->actingAs($user)
            ->patch(route('transactions.update', $transaction), [
                'type' => TransactionType::Expense->value,
                'amount' => '2500.75',
                'category_id' => null,
                'description' => 'Updated transaction',
                'occurred_at' => '2026-08-02 10:30:00',
            ])
            ->assertRedirect(route('transactions.index'));

        Event::assertDispatched(
            TransactionUpdated::class,
            fn (TransactionUpdated $event): bool => $event->accountUuid === $account->uuid
                && $event->transaction->is($transaction)
                && $event->transaction->amount === '2500.75',
        );
    }

    public function test_deleting_transaction_dispatches_transaction_deleted_event(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
        ]);

        Event::fake([TransactionDeleted::class]);

        $this->actingAs($user)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'));

        Event::assertDispatched(
            TransactionDeleted::class,
            fn (TransactionDeleted $event): bool => $event->accountUuid === $account->uuid
                && $event->transactionUuid === $transaction->uuid,
        );
        $this->assertModelMissing($transaction);
    }
}
