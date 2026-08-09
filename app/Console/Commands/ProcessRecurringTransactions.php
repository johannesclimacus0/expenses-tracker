<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\RecurringTransactions\ProcessRecurringTransaction;
use App\Models\RecurringTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('transactions:process-recurring')]
#[Description('Создаёт регулярные транзакции')]
final class ProcessRecurringTransactions extends Command
{
    private const MAX_CATCH_UP = 100;

    public function handle(ProcessRecurringTransaction $process): int
    {
        $now = CarbonImmutable::now();

        $created = 0;

        RecurringTransaction::query()
            ->where('is_active', true)
            ->where('next_run_at', '<=', $now)
            ->chunkById(100, function ($recurringTransactions) use ($process, $now, &$created,
            ): void {
                foreach ($recurringTransactions as $recurringTransaction) {
                    for (
                        $attempt = 0;
                        $attempt < self::MAX_CATCH_UP;
                        $attempt++
                    ) {
                        $transaction = $process->handle(
                            $recurringTransaction,
                            $now,
                        );

                        if ($transaction === null) {
                            break;
                        }

                        $created++;
                        $recurringTransaction->refresh();
                    }
                }
            },
            );

        $this->info("Создано транзакций: {$created}");

        return self::SUCCESS;
    }
}
