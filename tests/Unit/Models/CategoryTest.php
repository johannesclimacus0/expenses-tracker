<?php

namespace Tests\Unit\Models;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_belongs_to_user_and_has_transactions_and_budgets(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);
        $budget = Budget::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $category->user());
        $this->assertInstanceOf(HasMany::class, $category->transactions());
        $this->assertInstanceOf(HasMany::class, $category->budgets());
        $this->assertTrue($category->user->is($user));
        $this->assertTrue($category->transactions->contains($transaction));
        $this->assertTrue($category->budgets->contains($budget));
    }

    public function test_type_is_cast_to_enum(): void
    {
        $category = Category::factory()->create([
            'type' => TransactionType::Income->value,
        ])->fresh();

        $this->assertSame(TransactionType::Income, $category->type);
    }

    public function test_uuid_is_generated_and_used_as_route_key(): void
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->uuid);
        $this->assertSame('uuid', $category->getRouteKeyName());
        $this->assertSame($category->uuid, $category->getRouteKey());
    }
}
