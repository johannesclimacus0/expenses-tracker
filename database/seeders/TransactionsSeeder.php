<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionsSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->with('categories')
            ->eachById(function (User $user): void {
                $categoriesByType = $user->categories->groupBy(
                    fn ($category) => $category->type->value,
                );

                Transaction::factory()
                    ->count(fake()->numberBetween(20, 25))
                    ->for($user)
                    ->state(function () use ($categoriesByType): array {
                        $type = fake()->randomElement(TransactionType::cases());
                        $categories = $categoriesByType->get($type->value, collect());
                        $category = $categories->isNotEmpty() && fake()->boolean(75)
                            ? $categories->random()
                            : null;

                        return [
                            'type' => $type,
                            'category_id' => $category?->id,
                        ];
                    })
                    ->create();
            });
    }
}
