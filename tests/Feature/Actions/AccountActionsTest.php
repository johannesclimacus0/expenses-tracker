<?php

declare(strict_types=1);

namespace Actions;

use App\Actions\Accounts\CreatePersonalAccount;
use App\Actions\Accounts\ResolveCurrentAccount;
use App\Enums\AccountRole;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class AccountActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_personal_account_for_user(): void
    {
        $user = User::factory()->create();
        $user->accounts()->detach();

        $account = app(CreatePersonalAccount::class)->handle($user);

        $this->assertSame('Личный счет', $account->name);
        $this->assertSame(Currency::Rub, $account->currency);
        $this->assertDatabaseHas('account_members', [
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'role' => AccountRole::Owner->value,
        ]);
    }

    public function test_it_uses_user_settings_currency_for_personal_account(): void
    {
        $user = User::factory()->create();
        $user->accounts()->detach();
        $user->settings()->create([
            'currency' => Currency::Eur,
        ]);

        $account = app(CreatePersonalAccount::class)->handle($user);

        $this->assertSame(Currency::Eur, $account->currency);
    }

    public function test_it_does_not_create_duplicate_personal_account_if_user_already_has_one(): void
    {
        $user = User::factory()->create();
        $existingAccount = $user->accounts()->firstOrFail();

        $account = app(CreatePersonalAccount::class)->handle($user);

        $this->assertTrue($account->is($existingAccount));
        $this->assertSame(1, $user->accounts()->count());
    }

    public function test_resolver_creates_account_for_user_without_accounts(): void
    {
        $user = User::factory()->create();
        $user->accounts()->detach();

        $account = app(ResolveCurrentAccount::class)->handle($user);

        $this->assertTrue($user->fresh()->accounts->contains($account));
    }

    public function test_resolver_returns_existing_account(): void
    {
        $user = User::factory()->create();
        $existingAccount = $user->accounts()->firstOrFail();

        $account = app(ResolveCurrentAccount::class)->handle($user);

        $this->assertTrue($account->is($existingAccount));
    }

    public function test_resolver_returns_active_account_from_settings(): void
    {
        $user = User::factory()->create();
        $activeAccount = Account::factory()->create();
        $user->accounts()->attach($activeAccount, [
            'role' => AccountRole::Member,
        ]);
        $user->settings()->create([
            'active_account_id' => $activeAccount->getKey(),
        ]);

        $account = app(ResolveCurrentAccount::class)->handle($user);

        $this->assertTrue($account->is($activeAccount));
    }

    public function test_resolver_ignores_active_account_that_user_cannot_access(): void
    {
        $user = User::factory()->create();
        $existingAccount = $user->accounts()->firstOrFail();
        $foreignAccount = Account::factory()->create();
        $user->settings()->create([
            'active_account_id' => $foreignAccount->getKey(),
        ]);

        $account = app(ResolveCurrentAccount::class)->handle($user);

        $this->assertTrue($account->is($existingAccount));
    }

    public function test_registered_user_receives_personal_account(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Kisa',
            'email' => 'kisa@example.com',
            'password' => 'Codex-August-2026!Kisa#47',
            'password_confirmation' => 'Codex-August-2026!Kisa#47',
        ])->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'kisa@example.com')->firstOrFail();
        $account = $user->accounts()->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Личный счет', $account->name);
        $this->assertDatabaseHas('account_members', [
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'role' => AccountRole::Owner->value,
        ]);
    }

    public function test_user_factory_creates_personal_account(): void
    {
        $user = User::factory()->create();
        $account = $user->accounts()->firstOrFail();

        $this->assertInstanceOf(Account::class, $account);
        $this->assertDatabaseHas('account_members', [
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'role' => AccountRole::Owner->value,
        ]);
    }
}
