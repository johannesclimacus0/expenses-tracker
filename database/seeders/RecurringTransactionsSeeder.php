<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\RecurringTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

final class RecurringTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        Account::query()
            ->with(['members:id,account_id,user_id', 'categories'])
            ->eachById(function (Account $account): void {
                if ($account->members->isEmpty()) {
                    return;
                }

                $categoriesByType = $account->categories->groupBy(
                    fn ($category) => $category->type->value,
                );

                RecurringTransaction::factory()
                    ->count(6)
                    ->sequence(function (Sequence $sequence) use ($account, $categoriesByType): array {
                        $type = fake()->randomElement(TransactionType::cases());
                        $period = fake()->randomElement(RecurringPeriod::cases());
                        $categories = $categoriesByType->get($type->value, collect());
                        $category = $categories->isNotEmpty() && fake()->boolean(75)
                            ? $categories->random()
                            : null;

                        $isActive = $sequence->index < 4;
                        $isFuture = in_array($sequence->index, [2, 3], true);
                        $startsAt = $isFuture
                            ? CarbonImmutable::now()->startOfMinute()->addDays(fake()->numberBetween(1, 30))
                            : $this->pastOccurrence($period);

                        return [
                            'account_id' => $account->getKey(),
                            'user_id' => $account->members->random()->user_id,
                            'type' => $type,
                            'category_id' => $category?->id,
                            'period' => $period,
                            'starts_at' => $startsAt,
                            'next_run_at' => $startsAt,
                            'last_run_at' => null,
                            'is_active' => $isActive,
                        ];
                    })
                    ->create();
            });
    }

    private function pastOccurrence(RecurringPeriod $period): CarbonImmutable
    {
        $periodsAgo = fake()->numberBetween(1, 3);
        $now = CarbonImmutable::now()->startOfMinute();

        return match ($period) {
            RecurringPeriod::Weekly => $now->subWeeks($periodsAgo),
            RecurringPeriod::Biweekly => $now->subWeeks($periodsAgo * 2),
            RecurringPeriod::Monthly => $now->subMonthsNoOverflow($periodsAgo),
            RecurringPeriod::Yearly => $now->subYearsNoOverflow($periodsAgo),
        };
    }
}
