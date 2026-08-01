<?php

namespace App\Actions\Transactions;

use App\Models\Transaction;

final class DeleteTransaction
{
    public function handle(Transaction $transaction): void
    {
        $transaction->delete();
    }
}
