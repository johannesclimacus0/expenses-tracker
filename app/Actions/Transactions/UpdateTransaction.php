<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\DTOs\Transactions\TransactionData;
use App\Models\Transaction;

final class UpdateTransaction
{
    public function handle(Transaction $transaction, TransactionData $data): Transaction
    {
        $transaction->update([
            'type' => $data->type,
            'description' => $data->description,
            'category_id' => $data->categoryId,
            'amount' => $data->amount,
            'occurred_at' => $data->occurredAt,
        ]);

        return $transaction->refresh();
    }
}
