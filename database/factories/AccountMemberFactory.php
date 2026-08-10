<?php

namespace Database\Factories;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountMember>
 */
class AccountMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'role' => $this->faker->randomElement(AccountRole::cases()),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => [
            'role' => AccountRole::Owner,
        ]);
    }

    public function member(): static
    {
        return $this->state(fn () => [
            'role' => AccountRole::Member,
        ]);
    }
}
