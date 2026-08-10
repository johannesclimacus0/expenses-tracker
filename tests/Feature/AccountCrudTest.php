<?php

namespace Tests\Feature;

use App\Enums\AccountRole;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_account_and_it_becomes_active(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('accounts.store'), [
            'name' => 'Семейный счет',
            'currency' => Currency::Eur->value,
        ])->assertRedirect(route('accounts.index'));

        $account = Account::query()->where('name', 'Семейный счет')->firstOrFail();
        $this->assertSame(Currency::Eur, $account->currency);
        $this->assertDatabaseHas('account_members', [
            'account_id' => $account->id,
            'user_id' => $user->id,
            'role' => AccountRole::Owner->value,
        ]);
        $this->assertSame($account->id, $user->settings()->value('active_account_id'));
    }

    public function test_owner_can_update_account(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->firstOrFail();

        $this->actingAs($user)->patch(route('accounts.update', $account), [
            'name' => 'Дом',
            'currency' => Currency::Usd->value,
        ])->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'name' => 'Дом', 'currency' => 'USD']);
    }

    public function test_member_cannot_update_or_delete_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $account->members()->create(['user_id' => $user->id, 'role' => AccountRole::Member]);

        $this->actingAs($user)->patch(route('accounts.update', $account), [
            'name' => 'Чужой счет',
            'currency' => Currency::Rub->value,
        ])->assertForbidden();

        $this->actingAs($user)->delete(route('accounts.destroy', $account))->assertForbidden();
    }

    public function test_owner_can_delete_account_when_another_account_remains(): void
    {
        $user = User::factory()->create();
        $personal = $user->accounts()->firstOrFail();
        $account = Account::factory()->create();
        $account->members()->create(['user_id' => $user->id, 'role' => AccountRole::Owner]);
        $user->settings()->updateOrCreate([], ['active_account_id' => $account->id]);

        $this->actingAs($user)
            ->delete(route('accounts.destroy', $account))
            ->assertRedirect(route('accounts.index'));

        $this->assertModelMissing($account);
        $this->assertNull($user->settings()->value('active_account_id'));
        $this->assertModelExists($personal);
    }

    public function test_user_cannot_delete_last_account(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->firstOrFail();

        $this->actingAs($user)->delete(route('accounts.destroy', $account))->assertForbidden();

        $this->assertModelExists($account);
    }
}
