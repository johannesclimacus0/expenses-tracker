<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\DTOs\RecurringTransactions\RecurringTransactionData;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Services\RecurringTransactions\RecurringScheduleService;
use Carbon\CarbonImmutable;

final readonly class CreateRecurringTransaction
{
    public function __construct(
        private RecurringScheduleService $schedule,
        private ResolveCurrentAccount $accounts,
    ) {}

    public function handle(User $user, RecurringTransactionData $data): RecurringTransaction
    {
        $account = $this->accounts->handle($user);

        return $user->recurringTransactions()->create([
            'account_id' => $account->getKey(),
            'type' => $data->type,
            'amount' => $data->amount,
            'category_id' => $data->categoryId,
            'description' => $data->description,
            'period' => $data->period,
            'starts_at' => $data->startsAt,
            'next_run_at' => $this->schedule->nextRunAt(
                $data->startsAt,
                $data->period,
                CarbonImmutable::now(),
            ),
            'is_active' => $data->isActive,
        ]);
    }
}
