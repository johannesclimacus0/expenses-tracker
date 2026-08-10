<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'currency' => $this->faker->randomElement(Currency::cases()),
        ];
    }
}
