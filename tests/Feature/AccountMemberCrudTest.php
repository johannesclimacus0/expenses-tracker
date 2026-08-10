<?php

namespace Tests\Feature;

use App\Enums\AccountRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountMemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_registered_user_by_email(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create(['email' => 'member@example.com']);
        $account = $owner->accounts()->firstOrFail();

        $this->actingAs($owner)->post(route('accounts.members.store', $account), [
            'email' => ' MEMBER@example.com ',
        ])->assertRedirect(route('accounts.edit', $account));

        $this->assertDatabaseHas('account_members', [
            'account_id' => $account->id,
            'user_id' => $user->id,
            'role' => AccountRole::Member->value,
        ]);
    }

    public function test_user_cannot_be_added_twice(): void
    {
        $owner = User::factory()->create();
        $account = $owner->accounts()->firstOrFail();

        $this->actingAs($owner)->from(route('accounts.edit', $account))
            ->post(route('accounts.members.store', $account), ['email' => $owner->email])
            ->assertRedirect(route('accounts.edit', $account))
            ->assertSessionHasErrors('email');
    }

    public function test_member_cannot_manage_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $candidate = User::factory()->create();
        $account = $owner->accounts()->firstOrFail();
        $membership = $account->members()->create([
            'user_id' => $member->id,
            'role' => AccountRole::Member,
        ]);

        $this->actingAs($member)->post(route('accounts.members.store', $account), [
            'email' => $candidate->email,
        ])->assertForbidden();

        $this->actingAs($member)
            ->delete(route('accounts.members.destroy', [$account, $membership]))
            ->assertForbidden();
    }

    public function test_owner_can_remove_member_and_their_active_account_is_cleared(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $account = $owner->accounts()->firstOrFail();
        $membership = $account->members()->create([
            'user_id' => $member->id,
            'role' => AccountRole::Member,
        ]);
        $member->settings()->updateOrCreate([], ['active_account_id' => $account->id]);

        $this->actingAs($owner)
            ->delete(route('accounts.members.destroy', [$account, $membership]))
            ->assertRedirect(route('accounts.edit', $account));

        $this->assertModelMissing($membership);
        $this->assertNull($member->settings()->value('active_account_id'));
    }

    public function test_owner_membership_cannot_be_removed(): void
    {
        $owner = User::factory()->create();
        $account = $owner->accounts()->firstOrFail();
        $membership = $account->members()->where('user_id', $owner->id)->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('accounts.members.destroy', [$account, $membership]))
            ->assertForbidden();

        $this->assertModelExists($membership);
    }
}
