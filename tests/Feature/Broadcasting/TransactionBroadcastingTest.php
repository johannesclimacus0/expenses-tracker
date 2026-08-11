<?php

declare(strict_types=1);

namespace Tests\Feature\Broadcasting;

use App\Enums\TransactionType;
use App\Events\TransactionCreated;
use App\Events\TransactionDeleted;
use App\Events\TransactionUpdated;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TransactionBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => 'test-app',
        ]);

        require base_path('routes/channels.php');
    }

    public function test_transaction_created_broadcasts_on_private_account_channel(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
        ]);

        $event = new TransactionCreated($transaction, $account->uuid);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-accounts.' . $account->uuid, $channels[0]->name);
    }

    public function test_transaction_created_contains_safe_payload(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
            'type' => TransactionType::Expense,
            'amount' => '1250.50',
            'description' => 'Продукты',
            'occurred_at' => '2026-08-01 14:32:00',
        ]);

        $event = new TransactionCreated($transaction, $account->uuid);

        $this->assertSame([
            'uuid' => $transaction->uuid,
            'type' => TransactionType::Expense->value,
            'amount' => '1250.50',
            'description' => 'Продукты',
            'occurred_at' => $transaction->occurred_at->toIso8601String(),
        ], $event->broadcastWith());
    }

    public function test_transaction_updated_broadcasts_on_private_account_channel(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
        ]);

        $event = new TransactionUpdated($transaction, $account->uuid);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-accounts.' . $account->uuid, $channels[0]->name);
        $this->assertSame('transaction.updated', $event->broadcastAs());
    }

    public function test_transaction_updated_contains_safe_payload(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
            'type' => TransactionType::Income,
            'amount' => '8750.25',
            'description' => 'Updated income',
            'occurred_at' => '2026-08-02 09:15:00',
        ]);

        $event = new TransactionUpdated($transaction, $account->uuid);

        $this->assertSame([
            'uuid' => $transaction->uuid,
            'type' => TransactionType::Income->value,
            'amount' => '8750.25',
            'description' => 'Updated income',
            'occurred_at' => $transaction->occurred_at->toIso8601String(),
        ], $event->broadcastWith());
    }

    public function test_transaction_deleted_broadcasts_on_private_account_channel(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
        ]);

        $event = new TransactionDeleted($transaction->uuid, $account->uuid);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-accounts.' . $account->uuid, $channels[0]->name);
        $this->assertSame('transaction.deleted', $event->broadcastAs());
    }

    public function test_transaction_deleted_contains_only_transaction_uuid(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $transaction = Transaction::factory()->for($user)->create([
            'account_id' => $account->id,
        ]);

        $event = new TransactionDeleted($transaction->uuid, $account->uuid);

        $this->assertSame([
            'uuid' => $transaction->uuid,
        ], $event->broadcastWith());
    }

    public function test_account_member_can_authorize_private_account_channel(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();

        $this->actingAs($user)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-accounts.' . $account->uuid,
                'socket_id' => '123.456',
            ])
            ->assertOk();
    }

    public function test_foreign_user_cannot_authorize_private_account_channel(): void
    {
        $accountOwner = User::factory()->create();
        $account = $accountOwner->accounts()->sole();
        $foreignUser = User::factory()->create();

        $this->actingAs($foreignUser)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-accounts.' . $account->uuid,
                'socket_id' => '123.456',
            ])
            ->assertForbidden();
    }
}
