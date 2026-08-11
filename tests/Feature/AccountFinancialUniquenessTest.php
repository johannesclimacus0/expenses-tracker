<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountRole;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountFinancialUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_name_and_type_are_unique_within_account(): void
    {
        [$owner, $member, $account] = $this->accountWithTwoMembers();

        Category::factory()->for($owner)->for($account)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);

        $this->expectException(QueryException::class);

        Category::factory()->for($member)->for($account)->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);
    }

    public function test_same_category_name_and_type_are_allowed_in_different_accounts(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        Category::factory()->for($firstUser)->for($firstUser->accounts()->sole())->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);
        Category::factory()->for($secondUser)->for($secondUser->accounts()->sole())->create([
            'name' => 'Продукты',
            'type' => TransactionType::Expense,
        ]);

        $this->assertDatabaseCount('categories', 2);
    }

    public function test_overall_budget_is_unique_within_account_and_month(): void
    {
        [$owner, $member, $account] = $this->accountWithTwoMembers();

        Budget::factory()->for($owner)->for($account)->create([
            'category_id' => null,
            'month' => '2026-08-01',
        ]);

        $this->expectException(QueryException::class);

        Budget::factory()->for($member)->for($account)->create([
            'category_id' => null,
            'month' => '2026-08-01',
        ]);
    }

    public function test_category_budget_is_unique_within_account_category_and_month(): void
    {
        [$owner, $member, $account] = $this->accountWithTwoMembers();
        $category = Category::factory()->for($owner)->for($account)->create([
            'type' => TransactionType::Expense,
        ]);

        Budget::factory()->for($owner)->for($account)->create([
            'category_id' => $category->id,
            'month' => '2026-08-01',
        ]);

        $this->expectException(QueryException::class);

        Budget::factory()->for($member)->for($account)->create([
            'category_id' => $category->id,
            'month' => '2026-08-01',
        ]);
    }

    public function test_same_budget_scope_is_allowed_in_different_accounts(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        Budget::factory()->for($firstUser)->for($firstUser->accounts()->sole())->create([
            'category_id' => null,
            'month' => '2026-08-01',
        ]);
        Budget::factory()->for($secondUser)->for($secondUser->accounts()->sole())->create([
            'category_id' => null,
            'month' => '2026-08-01',
        ]);

        $this->assertDatabaseCount('budgets', 2);
    }

    /**
     * @return array{User, User, Account}
     */
    private function accountWithTwoMembers(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $account = $owner->accounts()->sole();

        $account->members()->create([
            'user_id' => $member->id,
            'role' => AccountRole::Member,
        ]);

        return [$owner, $member, $account];
    }
}
