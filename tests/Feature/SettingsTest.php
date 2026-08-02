<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\DashboardPeriod;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Budgets\BudgetUsageService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_settings(): void
    {
        $this->patch(route('settings.update'), $this->validSettings())
            ->assertRedirect(route('login'));
    }

    public function test_user_can_open_settings_with_defaults(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Период обзора')
            ->assertSee('Сохранить настройки');

        $this->assertDatabaseMissing('user_settings', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_create_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.update'), $this->validSettings())
            ->assertRedirect()
            ->assertSessionHas('status', 'settings-updated');

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'currency' => Currency::Usd->value,
            'dashboard_period' => DashboardPeriod::Quarter->value,
            'transactions_per_page' => 20,
            'budget_warning_percent' => 75,
            'show_cents' => false,
        ]);
    }

    public function test_repeated_save_updates_the_same_settings_record(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.update'), $this->validSettings());

        $this->actingAs($user)
            ->patch(route('settings.update'), [
                ...$this->validSettings(),
                'currency' => Currency::Eur->value,
                'dashboard_period' => DashboardPeriod::Year->value,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('user_settings', 1);
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'currency' => Currency::Eur->value,
            'dashboard_period' => DashboardPeriod::Year->value,
        ]);
    }

    public function test_unknown_enum_values_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('settings.index'))
            ->patch(route('settings.update'), [
                ...$this->validSettings(),
                'currency' => 'BTC',
                'dashboard_period' => 'week',
            ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors(['currency', 'dashboard_period']);

        $this->assertDatabaseMissing('user_settings', [
            'user_id' => $user->id,
        ]);
    }

    public function test_saved_currency_and_cents_settings_are_used_for_money(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->for($user)->income()->create([
            'amount' => '1000.00',
            'occurred_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('settings.update'), $this->validSettings());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 000 $')
            ->assertDontSee('1 000,00');
    }

    public function test_user_can_update_profile_without_losing_verification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => 'Новое имя',
                'email' => $user->email,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'profile-updated');

        $user->refresh();

        $this->assertSame('Новое имя', $user->name);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_changing_email_requires_verification_again(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.profile.update'), [
                'name' => $user->name,
                'email' => 'new@example.com',
            ])
            ->assertRedirect(route('verification.notice'));

        $user->refresh();

        $this->assertSame('new@example.com', $user->email);
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create();
        $rememberToken = $user->remember_token;

        $this->actingAs($user)
            ->patch(route('settings.password.update'), [
                'current_password' => 'password',
                'password' => 'Codex-August-2026!Kisa#47',
                'password_confirmation' => 'Codex-August-2026!Kisa#47',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'password-updated');

        $user->refresh();

        $this->assertTrue(Hash::check('Codex-August-2026!Kisa#47', $user->password));
        $this->assertNotSame($rememberToken, $user->remember_token);
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('settings.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'Codex-August-2026!Kisa#47',
                'password_confirmation' => 'Codex-August-2026!Kisa#47',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_dashboard_uses_saved_period_and_query_can_override_it(): void
    {
        $user = User::factory()->create();
        $user->settings()->create([
            'dashboard_period' => DashboardPeriod::Quarter,
        ]);
        Transaction::factory()->for($user)->income()->create([
            'amount' => '1000.00',
            'occurred_at' => now(),
        ]);
        Transaction::factory()->for($user)->income()->create([
            'amount' => '500.00',
            'occurred_at' => now()->subMonths(2),
        ]);
        Transaction::factory()->for($user)->income()->create([
            'amount' => '800.00',
            'occurred_at' => now()->subMonths(4),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 500,00');

        $this->get(route('dashboard', ['period' => DashboardPeriod::Month->value]))
            ->assertOk()
            ->assertSee('1 000,00')
            ->assertDontSee('1 500,00');
    }

    public function test_transaction_pagination_uses_saved_page_size(): void
    {
        $user = User::factory()->create();
        $user->settings()->create([
            'transactions_per_page' => 20,
        ]);
        Transaction::factory()->count(15)->for($user)->create();

        $this->actingAs($user)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertViewHas(
                'transactions',
                fn ($transactions) => $transactions->count() === 15
                    && $transactions->perPage() === 20,
            );
    }

    public function test_budget_warning_uses_saved_threshold(): void
    {
        $user = User::factory()->create();
        $user->settings()->create([
            'budget_warning_percent' => 50,
        ]);
        Budget::factory()->for($user)->create([
            'amount' => '500.00',
            'month' => now()->startOfMonth(),
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'amount' => '300.00',
            'occurred_at' => now(),
        ]);

        $usage = app(BudgetUsageService::class)->forMonth(
            $user,
            now()->startOfMonth(),
        );

        $this->assertTrue($usage->first()->warning);
        $this->assertFalse($usage->first()->exceeded);
    }

    private function validSettings(): array
    {
        return [
            'currency' => Currency::Usd->value,
            'dashboard_period' => DashboardPeriod::Quarter->value,
            'transactions_per_page' => 20,
            'budget_warning_percent' => 75,
            'show_cents' => false,
        ];
    }
}
