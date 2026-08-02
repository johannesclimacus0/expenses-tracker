<?php

namespace Tests\Unit\Models;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_exposes_expected_relations(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(HasMany::class, $user->categories());
        $this->assertInstanceOf(HasMany::class, $user->transactions());
        $this->assertInstanceOf(HasMany::class, $user->budgets());
        $this->assertInstanceOf(HasOne::class, $user->settings());
    }

    public function test_user_relations_return_owned_models(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->create();
        $budget = Budget::factory()->for($user)->create();
        $settings = $user->settings()->create();

        $this->assertTrue($user->categories->contains($category));
        $this->assertTrue($user->transactions->contains($transaction));
        $this->assertTrue($user->budgets->contains($budget));
        $this->assertTrue($user->settings->is($settings));
    }

    public function test_password_is_hashed_and_sensitive_attributes_are_hidden(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password',
        ]);

        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    public function test_verification_date_is_immutable_and_user_requires_email_verification(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(CarbonImmutable::class, $user->email_verified_at);
        $this->assertInstanceOf(MustVerifyEmail::class, $user);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_owned_models_are_deleted_with_user(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $transaction = Transaction::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);
        $budget = Budget::factory()->for($user)->create([
            'category_id' => $category->id,
        ]);
        $settings = $user->settings()->create();

        $user->delete();

        $this->assertModelMissing($category);
        $this->assertModelMissing($transaction);
        $this->assertModelMissing($budget);
        $this->assertModelMissing($settings);
    }
}
