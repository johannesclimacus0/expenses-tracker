<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Models\RecurringTransaction;

final class DeleteRecurringTransaction
{
    public function handle(RecurringTransaction $recurringTransaction): void
    {
        $recurringTransaction->delete();
    }
}
