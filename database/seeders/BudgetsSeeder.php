<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use Illuminate\Database\Seeder;

class BudgetsSeeder extends Seeder
{
    public function run(): void
    {
        Account::query()
            ->with([
                'members:id,account_id,user_id',
                'categories' => fn ($query) => $query->where('type', TransactionType::Expense->value),
            ])
            ->eachById(function (Account $account): void {
                if ($account->members->isEmpty()) {
                    return;
                }

                foreach (range(0, 2) as $monthsAgo) {
                    $month = now()->subMonths($monthsAgo)->startOfMonth();
                    Budget::query()->firstOrCreate(
                        [
                            'account_id' => $account->getKey(),
                            'category_id' => null,
                            'month' => $month,
                        ],
                        [
                            'user_id' => $account->members->random()->user_id,
                            'amount' => fake()->randomFloat(2, 40000, 150000),
                        ],
                    );

                    $account->categories
                        ->shuffle()
                        ->take(5)
                        ->each(function ($category) use ($account, $month): void {
                            Budget::query()->firstOrCreate(
                                [
                                    'account_id' => $account->getKey(),
                                    'category_id' => $category->id,
                                    'month' => $month,
                                ],
                                [
                                    'user_id' => $account->members->random()->user_id,
                                    'amount' => fake()->randomFloat(2, 3000, 30000),
                                ],
                            );
                        });
                }
            });
    }
}
