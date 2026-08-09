<?php

namespace App\Http\Requests\RecurringTransactions;

use App\Models\RecurringTransaction;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $recurringTransaction = $this->route('recurring_transaction');

        return $recurringTransaction instanceof RecurringTransaction
            && ($this->user()?->can('delete', $recurringTransaction) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
