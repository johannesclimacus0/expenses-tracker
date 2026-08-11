<?php

namespace App\Actions\Transactions;

use App\Events\TransactionDeleted;
use App\Models\Transaction;

final class DeleteTransaction
{
    public function handle(Transaction $transaction): void
    {
        $transactionUuid = $transaction->uuid;
        $accountUuid = $transaction->account->uuid;

        $transaction->delete();

        TransactionDeleted::dispatch($transactionUuid, $accountUuid);
    }
}
