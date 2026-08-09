<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransactions;

use App\DTOs\RecurringTransactions\RecurringTransactionData;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactions\RecurringScheduleService;
use Carbon\CarbonImmutable;

final readonly class UpdateRecurringTransaction
{
    public function __construct(private RecurringScheduleService $schedule) {}

    public function handle(RecurringTransaction $recurringTransaction, RecurringTransactionData $data): RecurringTransaction
    {
        $scheduleChanged = $recurringTransaction->period !== $data->period
            || !$recurringTransaction->starts_at->equalTo($data->startsAt);
        $reactivated = !$recurringTransaction->is_active && $data->isActive;

        $attributes = [
            'type' => $data->type,
            'amount' => $data->amount,
            'category_id' => $data->categoryId,
            'description' => $data->description,
            'period' => $data->period,
            'starts_at' => $data->startsAt,
            'is_active' => $data->isActive,
        ];

        if ($scheduleChanged || $reactivated) {
            $attributes['next_run_at'] = $this->schedule->nextRunAt(
                $data->startsAt,
                $data->period,
                CarbonImmutable::now(),
            );
        }

        $recurringTransaction->update($attributes);

        return $recurringTransaction->refresh();
    }
}
