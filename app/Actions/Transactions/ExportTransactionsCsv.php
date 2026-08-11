<?php

namespace App\Actions\Transactions;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\Models\User;
use App\Services\Transactions\TransactionCsvService;

final class ExportTransactionsCsv
{
    public function __construct(
        private TransactionCsvService $csv,
        private ResolveCurrentAccount $resolveCurrentAccount,
    ) {}

    public function handle(User $user, mixed $stream): void
    {
        $account = $this->resolveCurrentAccount->handle($user);

        $transactions = $account->transactions()
            ->with('category')
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->lazy(500);

        $this->csv->write($transactions, $stream);
    }
}
