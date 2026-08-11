<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class TransactionsSeeder extends Seeder
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

                Transaction::factory()
                    ->count(fake()->numberBetween(20, 25))
                    ->sequence(function (Sequence $sequence) use ($account, $categoriesByType): array {
                        $type = fake()->randomElement(TransactionType::cases());
                        $categories = $categoriesByType->get($type->value, collect());
                        $category = $categories->isNotEmpty() && fake()->boolean(75)
                            ? $categories->random()
                            : null;

                        return [
                            'account_id' => $account->getKey(),
                            'user_id' => $account->members->random()->user_id,
                            'type' => $type,
                            'category_id' => $category?->id,
                            'occurred_at' => $sequence->index < 5
                                ? fake()->dateTimeBetween(now()->startOfMonth(), now())
                                : fake()->dateTimeBetween('-1 year'),
                        ];
                    })
                    ->create();
            });
    }
}
