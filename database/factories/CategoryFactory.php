<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Database\Factories\Concerns\CreatesAccountMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    use CreatesAccountMembership;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => fn (array $attributes) => $this->defaultAccountId($attributes),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(TransactionType::cases()),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category): void {
            $this->createAccountMembership($category->user_id, $category->account_id);
        });
    }
}
