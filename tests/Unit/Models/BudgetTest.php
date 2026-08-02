<?php

namespace Tests\Unit\Models;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_belongs_to_user_and_optional_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $budget = Budget::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $budget->user());
        $this->assertInstanceOf(BelongsTo::class, $budget->category());
        $this->assertTrue($budget->user->is($user));
        $this->assertTrue($budget->category->is($category));
    }

    public function test_budget_attributes_are_cast(): void
    {
        $budget = Budget::factory()->create([
            'amount' => '1500.5',
            'month' => '2026-08-01',
        ])->fresh();

        $this->assertSame('1500.50', $budget->amount);
        $this->assertInstanceOf(CarbonImmutable::class, $budget->month);
        $this->assertSame('2026-08-01', $budget->month->format('Y-m-d'));
    }

    public function test_is_overall_depends_on_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $overall = Budget::factory()->for($user)->create([
            'category_id' => null,
        ]);
        $categoryBudget = Budget::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);

        $this->assertTrue($overall->isOverall());
        $this->assertFalse($categoryBudget->isOverall());
    }

    public function test_uuid_is_generated_and_used_as_route_key(): void
    {
        $budget = Budget::factory()->create();

        $this->assertNotNull($budget->uuid);
        $this->assertSame('uuid', $budget->getRouteKeyName());
        $this->assertSame($budget->uuid, $budget->getRouteKey());
    }
}
