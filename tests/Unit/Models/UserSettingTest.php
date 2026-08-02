<?php

namespace Tests\Unit\Models;

use App\Enums\Currency;
use App\Enums\DashboardPeriod;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_one_settings_relation(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(HasOne::class, $user->settings());

        $settings = $user->settings()->create([
            'currency' => Currency::Usd,
        ]);

        $this->assertTrue($settings->user->is($user));
        $this->assertSame($user->id, $settings->user_id);
    }

    public function test_settings_belong_to_user(): void
    {
        $user = User::factory()->create();
        $settings = $user->settings()->create();

        $this->assertInstanceOf(BelongsTo::class, $settings->user());
        $this->assertTrue($settings->user->is($user));
    }

    public function test_enum_and_scalar_attributes_are_cast(): void
    {
        $user = User::factory()->create();
        $settings = $user->settings()->create([
            'currency' => Currency::Eur,
            'dashboard_period' => DashboardPeriod::Quarter,
            'transactions_per_page' => '20',
            'budget_warning_percent' => '75',
            'show_cents' => 0,
        ])->fresh();

        $this->assertSame(Currency::Eur, $settings->currency);
        $this->assertSame(DashboardPeriod::Quarter, $settings->dashboard_period);
        $this->assertSame(20, $settings->transactions_per_page);
        $this->assertSame(75, $settings->budget_warning_percent);
        $this->assertFalse($settings->show_cents);

        $this->assertDatabaseHas('user_settings', [
            'id' => $settings->id,
            'currency' => Currency::Eur->value,
            'dashboard_period' => DashboardPeriod::Quarter->value,
        ]);
    }

    public function test_user_receives_unsaved_default_settings(): void
    {
        $user = User::factory()->create();
        $settings = $user->settings;

        $this->assertInstanceOf(UserSetting::class, $settings);
        $this->assertFalse($settings->exists);
        $this->assertSame(Currency::Rub, $settings->currency);
        $this->assertSame(DashboardPeriod::Month, $settings->dashboard_period);
        $this->assertSame(10, $settings->transactions_per_page);
        $this->assertSame(80, $settings->budget_warning_percent);
        $this->assertTrue($settings->show_cents);
        $this->assertDatabaseMissing('user_settings', [
            'user_id' => $user->id,
        ]);
    }

    public function test_settings_are_deleted_with_user(): void
    {
        $user = User::factory()->create();
        $settings = $user->settings()->create();

        $user->delete();

        $this->assertDatabaseMissing('user_settings', [
            'id' => $settings->id,
        ]);
    }
}
