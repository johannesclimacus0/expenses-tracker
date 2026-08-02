<?php

namespace Tests\Unit\Models;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_belongs_to_user_and_optional_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $transaction->user());
        $this->assertInstanceOf(BelongsTo::class, $transaction->category());
        $this->assertTrue($transaction->user->is($user));
        $this->assertTrue($transaction->category->is($category));

        $withoutCategory = Transaction::factory()->for($user)->create([
            'category_id' => null,
        ]);

        $this->assertNull($withoutCategory->category);
    }

    public function test_transaction_attributes_are_cast(): void
    {
        $transaction = Transaction::factory()->create([
            'type' => TransactionType::Expense->value,
            'amount' => '125.5',
            'occurred_at' => '2026-08-01 12:30:00',
        ])->fresh();

        $this->assertSame(TransactionType::Expense, $transaction->type);
        $this->assertSame('125.50', $transaction->amount);
        $this->assertInstanceOf(CarbonImmutable::class, $transaction->occurred_at);
        $this->assertSame('2026-08-01 12:30:00', $transaction->occurred_at->format('Y-m-d H:i:s'));
    }

    public function test_uuid_is_generated_and_used_as_route_key(): void
    {
        $transaction = Transaction::factory()->create();

        $this->assertNotNull($transaction->uuid);
        $this->assertSame('uuid', $transaction->getRouteKeyName());
        $this->assertSame($transaction->uuid, $transaction->getRouteKey());
    }
}
