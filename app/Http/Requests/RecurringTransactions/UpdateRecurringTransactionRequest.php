<?php

namespace App\Http\Requests\RecurringTransactions;

use App\Models\RecurringTransaction;

class UpdateRecurringTransactionRequest extends StoreRecurringTransactionRequest
{
    public function authorize(): bool
    {
        $recurringTransaction = $this->route('recurring_transaction');

        return $recurringTransaction instanceof RecurringTransaction
            && ($this->user()?->can('update', $recurringTransaction) ?? false);
    }
}
