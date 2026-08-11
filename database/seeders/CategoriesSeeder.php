<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::query()
            ->with('members:id,account_id,user_id')
            ->eachById(function (Account $account): void {
                if ($account->members->isEmpty()) {
                    return;
                }

                Category::factory()
                    ->count(fake()->numberBetween(25, 50))
                    ->sequence(fn (): array => [
                        'account_id' => $account->getKey(),
                        'user_id' => $account->members->random()->user_id,
                    ])
                    ->create();
            });
    }
}
