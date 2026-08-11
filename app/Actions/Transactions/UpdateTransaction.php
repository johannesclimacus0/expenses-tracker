<?php

declare(strict_types=1);

namespace App\Actions\Transactions;

use App\DTOs\Transactions\TransactionData;
use App\Events\TransactionUpdated;
use App\Models\Transaction;

final class UpdateTransaction
{
    public function handle(Transaction $transaction, TransactionData $data): Transaction
    {
        $accountUuid = $transaction->account->uuid;

        $transaction->update([
            'type' => $data->type,
            'description' => $data->description,
            'category_id' => $data->categoryId,
            'amount' => $data->amount,
            'occurred_at' => $data->occurredAt,
        ]);

        $updatedTransaction = $transaction->refresh();

        TransactionUpdated::dispatch($updatedTransaction, $accountUuid);

        return $updatedTransaction;
    }
}
