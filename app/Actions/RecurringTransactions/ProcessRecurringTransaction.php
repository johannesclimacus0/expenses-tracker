<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Services\RecurringTransactions\RecurringScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class ProcessRecurringTransaction
{
    public function __construct(
        private RecurringScheduleService $schedule,
    ) {}

    public function handle(RecurringTransaction $recurringTransaction, CarbonImmutable $now): ?Transaction
    {
        return DB::transaction(function () use ($recurringTransaction, $now): ?Transaction {
            $lockedRecurringTransaction = RecurringTransaction::query()
                ->whereKey($recurringTransaction->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedRecurringTransaction->is_active
                || $lockedRecurringTransaction->next_run_at->greaterThan($now)) {
                return null;
            }

            $scheduledFor = $lockedRecurringTransaction->next_run_at;

            $transaction = $lockedRecurringTransaction->transactions()->make([
                'account_id' => $lockedRecurringTransaction->account_id,
                'type' => $lockedRecurringTransaction->type,
                'amount' => $lockedRecurringTransaction->amount,
                'category_id' => $lockedRecurringTransaction->category_id,
                'description' => $lockedRecurringTransaction->description,
                'occurred_at' => $scheduledFor,
                'scheduled_for' => $scheduledFor,
            ]);

            $transaction->user()->associate($lockedRecurringTransaction->user_id);
            $transaction->save();

            $lockedRecurringTransaction->update([
                'last_run_at' => $scheduledFor,
                'next_run_at' => $this->schedule->nextRunAt(
                    $lockedRecurringTransaction->starts_at,
                    $lockedRecurringTransaction->period,
                    $scheduledFor->addSecond(),
                ),
            ]);

            return $transaction;
        });
    }
}
