<?php

namespace Tests\Feature\Events;

use App\Enums\TransactionType;
use App\Events\TransactionCreated;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TransactionCreatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_transaction_dispatches_transaction_created_event(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $category = Category::factory()->for($user)->create([
            'account_id' => $account->id,
            'type' => TransactionType::Expense,
        ]);

        Event::fake([TransactionCreated::class]);

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                'type' => TransactionType::Expense->value,
                'amount' => '1250.50',
                'category_id' => $category->id,
                'description' => 'Test Transaction',
                'occurred_at' => '2026-08-01 14:32',
            ])->assertRedirect(route('transactions.index'));
        Event::assertDispatched(TransactionCreated::class,
            function (TransactionCreated $event) use ($account) {
                return $event->transaction->exists && $event->accountUuid === $account->uuid;
            });
    }
}
