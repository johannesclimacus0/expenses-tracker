<?php

namespace App\Actions\Transactions;

use App\Models\User;
use App\Services\Transactions\TransactionCsvService;

final class ExportTransactionsCsv
{
    public function __construct(private TransactionCsvService $csv) {}

    public function handle(User $user, mixed $stream): void
    {
        $transactions = $user->transactions()
            ->with('category')
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->lazy(500);

        $this->csv->write($transactions, $stream);
    }
}
