<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TransactionUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Transaction $transaction,
        public string $accountUuid,
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('accounts.' . $this->accountUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid' => $this->transaction->uuid,
            'type' => $this->transaction->type->value,
            'amount' => $this->transaction->amount,
            'description' => $this->transaction->description,
            'occurred_at' => $this->transaction->occurred_at->toIso8601String(),
        ];
    }
}
