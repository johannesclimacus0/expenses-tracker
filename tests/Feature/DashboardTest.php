<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_settings(): void
    {
        $this->get(route('settings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_open_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee('Интерфейс')
            ->assertSee('Безопасность');
    }

    public function test_dashboard_shows_current_month_totals_budgets_and_latest_transactions(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->for($user)->income()->create([
            'amount' => '1000.00',
            'description' => 'Доход месяца',
            'occurred_at' => now()->startOfMonth()->addDay(),
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'amount' => '250.00',
            'description' => 'Расход месяца',
            'occurred_at' => now()->startOfMonth()->addDays(2),
        ]);
        Transaction::factory()->for($user)->expense()->create([
            'amount' => '999.00',
            'description' => 'Старый расход',
            'occurred_at' => now()->subMonth()->startOfMonth(),
        ]);
        Budget::factory()->for($user)->create([
            'amount' => '500.00',
            'month' => now()->startOfMonth(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 000,00')
            ->assertSee('250,00')
            ->assertSee('750,00')
            ->assertSee('Доход месяца')
            ->assertSee('Расход месяца');
    }
}
