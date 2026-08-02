<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Seeder;

class BudgetsSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->with(['categories' => fn ($query) => $query->where('type', TransactionType::Expense->value)])
            ->eachById(function (User $user): void {
                foreach (range(0, 2) as $monthsAgo) {
                    $month = now()->subMonths($monthsAgo)->startOfMonth();
                    Budget::query()->firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'category_id' => null,
                            'month' => $month,
                        ],
                        ['amount' => fake()->randomFloat(2, 40000, 150000)],
                    );

                    $user->categories
                        ->shuffle()
                        ->take(5)
                        ->each(function ($category) use ($user, $month): void {
                            Budget::query()->firstOrCreate(
                                [
                                    'user_id' => $user->id,
                                    'category_id' => $category->id,
                                    'month' => $month,
                                ],
                                ['amount' => fake()->randomFloat(2, 3000, 30000)],
                            );
                        });
                }
            });
    }
}
