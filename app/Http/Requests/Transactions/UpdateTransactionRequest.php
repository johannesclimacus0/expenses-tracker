<?php

namespace App\Http\Requests\Transactions;

use App\Models\Transaction;

class UpdateTransactionRequest extends StoreTransactionRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $transaction instanceof Transaction
            && ($this->user()?->can('update', $transaction) ?? false);
    }
}
