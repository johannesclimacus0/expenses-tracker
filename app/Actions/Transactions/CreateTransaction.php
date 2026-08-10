<?php

namespace App\Actions\Transactions;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\DTOs\Transactions\TransactionData;
use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\User;

final readonly class CreateTransaction
{
    public function __construct(private ResolveCurrentAccount $accounts) {}

    public function handle(User $user, TransactionData $data): Transaction
    {
        $account = $this->accounts->handle($user);

        $createdTransaction = $user->transactions()->create([
            'account_id' => $account->getKey(),
            'type' => $data->type,
            'description' => $data->description,
            'category_id' => $data->categoryId,
            'amount' => $data->amount,
            'occurred_at' => $data->occurredAt,
        ]);

        TransactionCreated::dispatch($createdTransaction, $account->uuid);

        return $createdTransaction;
    }
}
