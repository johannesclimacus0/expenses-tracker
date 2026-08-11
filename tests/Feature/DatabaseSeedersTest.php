<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\BudgetsSeeder;
use Database\Seeders\CategoriesSeeder;
use Database\Seeders\RecurringTransactionsSeeder;
use Database\Seeders\TransactionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_seeders_scope_data_to_accounts_and_use_members_as_authors(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $account = $owner->accounts()->sole();

        $account->members()->create([
            'user_id' => $member->getKey(),
            'role' => AccountRole::Member,
        ]);

        $this->seed([
            CategoriesSeeder::class,
            TransactionsSeeder::class,
            RecurringTransactionsSeeder::class,
            BudgetsSeeder::class,
        ]);

        $memberIds = $account->members()->pluck('user_id');

        $this->assertAccountAuthors($account, Category::class, $memberIds->all());
        $this->assertAccountAuthors($account, Transaction::class, $memberIds->all());
        $this->assertAccountAuthors($account, RecurringTransaction::class, $memberIds->all());
        $this->assertAccountAuthors($account, Budget::class, $memberIds->all());

        $this->assertFalse(
            Transaction::query()
                ->where('account_id', $account->getKey())
                ->whereHas(
                    'category',
                    fn ($query) => $query->where('account_id', '!=', $account->getKey()),
                )
                ->exists(),
        );
    }

    /**
     * @param  class-string<Category|Transaction|RecurringTransaction|Budget>  $model
     * @param  list<int>  $memberIds
     */
    private function assertAccountAuthors(Account $account, string $model, array $memberIds): void
    {
        $records = $model::query()->where('account_id', $account->getKey())->get();

        $this->assertNotEmpty($records);
        $this->assertTrue($records->every(
            fn ($record): bool => in_array($record->user_id, $memberIds, true),
        ));
    }
}
