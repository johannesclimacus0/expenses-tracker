<?php

namespace Tests\Feature;

use App\Enums\AccountRole;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_shows_available_accounts(): void
    {
        $user = User::factory()->create();
        $sharedAccount = Account::factory()->create(['name' => 'Семейный счет']);
        $sharedAccount->members()->create([
            'user_id' => $user->id,
            'role' => AccountRole::Member,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Текущий счет')
            ->assertSee('Личный счет')
            ->assertSee('Семейный счет');
    }

    public function test_user_can_switch_active_account(): void
    {
        $user = User::factory()->create();
        $sharedAccount = Account::factory()->create(['name' => 'Семейный счет']);
        $sharedAccount->members()->create([
            'user_id' => $user->id,
            'role' => AccountRole::Member,
        ]);

        $this->actingAs($user)
            ->patch(route('accounts.current.update'), ['account_uuid' => $sharedAccount->uuid])
            ->assertRedirect()
            ->assertSessionHas('status', 'Счет переключен');

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'active_account_id' => $sharedAccount->id,
        ]);
    }

    public function test_user_cannot_switch_to_foreign_account(): void
    {
        $user = User::factory()->create();
        $foreignAccount = Account::factory()->create();

        $this->actingAs($user)
            ->patch(route('accounts.current.update'), ['account_uuid' => $foreignAccount->uuid])
            ->assertForbidden();
    }

    public function test_transactions_page_uses_active_account(): void
    {
        $user = User::factory()->create();
        $personalAccount = $user->accounts()->first();
        $sharedAccount = Account::factory()->create();
        $sharedAccount->members()->create([
            'user_id' => $user->id,
            'role' => AccountRole::Member,
        ]);

        Transaction::factory()->for($user)->create([
            'account_id' => $personalAccount->id,
            'description' => 'Личная покупка',
        ]);
        Transaction::factory()->for($user)->create([
            'account_id' => $sharedAccount->id,
            'description' => 'Семейная покупка',
        ]);

        $user->settings()->updateOrCreate([], ['active_account_id' => $sharedAccount->id]);

        $this->actingAs($user)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('Семейная покупка')
            ->assertDontSee('Личная покупка');
    }

    public function test_categories_page_uses_active_account(): void
    {
        $user = User::factory()->create();
        $personalAccount = $user->accounts()->first();
        $sharedAccount = Account::factory()->create();
        $sharedAccount->members()->create([
            'user_id' => $user->id,
            'role' => AccountRole::Member,
        ]);

        Category::factory()->for($user)->create([
            'account_id' => $personalAccount->id,
            'name' => 'Личная категория',
            'type' => TransactionType::Expense,
        ]);
        Category::factory()->for($user)->create([
            'account_id' => $sharedAccount->id,
            'name' => 'Семейная категория',
            'type' => TransactionType::Expense,
        ]);

        $user->settings()->updateOrCreate([], ['active_account_id' => $sharedAccount->id]);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Семейная категория')
            ->assertDontSee('Личная категория');
    }
}
