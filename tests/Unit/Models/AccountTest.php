<?php

namespace Tests\Unit\Models;

use App\Enums\AccountRole;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_has_members_and_users(): void
    {
        $account = Account::factory()->create();
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $ownerMembership = AccountMember::factory()
            ->for($account)
            ->for($owner)
            ->owner()
            ->create();

        $memberMembership = AccountMember::factory()
            ->for($account)
            ->for($member)
            ->member()
            ->create();

        $this->assertInstanceOf(HasMany::class, $account->members());
        $this->assertInstanceOf(BelongsToMany::class, $account->users());
        $this->assertTrue($account->members->contains($ownerMembership));
        $this->assertTrue($account->members->contains($memberMembership));
        $this->assertTrue($account->users->contains($owner));
        $this->assertTrue($account->users->contains($member));
    }

    public function test_user_has_account_memberships_and_accounts(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $membership = AccountMember::factory()
            ->for($account)
            ->for($user)
            ->owner()
            ->create();

        $this->assertInstanceOf(HasMany::class, $user->accountMemberships());
        $this->assertInstanceOf(BelongsToMany::class, $user->accounts());
        $this->assertTrue($user->accountMemberships->contains($membership));
        $this->assertTrue($user->accounts->contains($account));
    }

    public function test_account_member_belongs_to_account_and_user(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $membership = AccountMember::factory()
            ->for($account)
            ->for($user)
            ->create();

        $this->assertInstanceOf(BelongsTo::class, $membership->account());
        $this->assertInstanceOf(BelongsTo::class, $membership->user());
        $this->assertTrue($membership->account->is($account));
        $this->assertTrue($membership->user->is($user));
    }

    public function test_account_attributes_are_cast(): void
    {
        $account = Account::factory()->create([
            'currency' => Currency::Rub->value,
        ])->fresh();

        $this->assertSame(Currency::Rub, $account->currency);
    }

    public function test_account_member_role_is_cast_to_enum(): void
    {
        $membership = AccountMember::factory()->create([
            'role' => AccountRole::Owner->value,
        ])->fresh();

        $this->assertSame(AccountRole::Owner, $membership->role);
    }

    public function test_uuid_is_generated_and_used_as_route_key(): void
    {
        $account = Account::factory()->create();

        $this->assertNotNull($account->uuid);
        $this->assertSame('uuid', $account->getRouteKeyName());
        $this->assertSame($account->uuid, $account->getRouteKey());
    }

    public function test_user_can_be_added_to_account_only_once(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();

        AccountMember::factory()
            ->for($account)
            ->for($user)
            ->owner()
            ->create();

        $this->expectException(QueryException::class);

        AccountMember::factory()
            ->for($account)
            ->for($user)
            ->member()
            ->create();
    }

    public function test_account_has_financial_relation(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();

        $category = Category::factory()
            ->for($user)
            ->for($account)
            ->create();
        $transaction = Transaction::factory()
            ->for($user)
            ->for($account)
            ->create([
                'category_id' => $category->id,
            ]);
        $budget = Budget::factory()
            ->for($user)
            ->for($account)
            ->create([
                'category_id' => $category->id,
            ]);

        $recurringTransaction = RecurringTransaction::factory()
            ->for($user)
            ->for($account)
            ->create([
                'category_id' => $category->id,
            ]);

        $goal = Goal::factory()
            ->for($user)
            ->for($account)
            ->create();

        $this->assertInstanceOf(HasMany::class, $account->categories());
        $this->assertInstanceOf(HasMany::class, $account->transactions());
        $this->assertInstanceOf(HasMany::class, $account->budgets());
        $this->assertInstanceOf(HasMany::class, $account->recurringTransactions());
        $this->assertInstanceOf(HasMany::class, $account->goals());

        $this->assertTrue($account->categories->contains($category));
        $this->assertTrue($account->transactions->contains($transaction));
        $this->assertTrue($account->budgets->contains($budget));
        $this->assertTrue($account->recurringTransactions->contains($recurringTransaction));
        $this->assertTrue($account->goals->contains($goal));
    }

    public function test_financial_model_factory_creates_matching_user_and_account(): void
    {
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->create();
        $budget = Budget::factory()->create();
        $recurringTransaction = RecurringTransaction::factory()->create();
        $goal = Goal::factory()->create();

        $models = [$category, $transaction, $budget, $recurringTransaction, $goal];
        foreach ($models as $model) {
            $this->assertNotNull($model->account_id);

            $this->assertDatabaseHas('account_members', [
                'account_id' => $model->account_id,
                'user_id' => $model->user_id,
            ]);
        }
    }

    public function test_deleting_account_cascades_to_financial_data(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->sole();
        $category = Category::factory()->for($user)->for($account)->create();
        $transaction = Transaction::factory()->for($user)->for($account)->create([
            'category_id' => $category->id,
        ]);
        $budget = Budget::factory()->for($user)->for($account)->create([
            'category_id' => $category->id,
        ]);
        $recurringTransaction = RecurringTransaction::factory()->for($user)->for($account)->create([
            'category_id' => $category->id,
        ]);
        $goal = Goal::factory()->for($user)->for($account)->create();
        $contribution = GoalContribution::factory()->for($goal)->create();

        $account->delete();

        $this->assertModelMissing($category);
        $this->assertModelMissing($transaction);
        $this->assertModelMissing($budget);
        $this->assertModelMissing($recurringTransaction);
        $this->assertModelMissing($goal);
        $this->assertModelMissing($contribution);
    }
}
