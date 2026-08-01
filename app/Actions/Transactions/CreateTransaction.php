<?php

namespace App\Actions\Transactions;

use App\DTOs\Transactions\TransactionData;
use App\Models\Transaction;
use App\Models\User;

final class CreateTransaction
{
    public function handle(User $user, TransactionData $data): Transaction
    {
        return $user->transactions()->create([
            'type' => $data->type,
            'description' => $data->description,
            'category_id' => $data->categoryId,
            'amount' => $data->amount,
            'occurred_at' => $data->occurredAt,
        ]);
    }
}
