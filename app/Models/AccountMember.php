<?php

namespace App\Models;

use App\Enums\AccountRole;
use Carbon\CarbonImmutable;
use Database\Factories\AccountMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $account_id
 * @property AccountRole $role
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Account $account
 * @property-read User $user
 *
 * @method static \Database\Factories\AccountMemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountMember whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['account_id', 'user_id', 'role'])]
class AccountMember extends Model
{
    /** @use HasFactory<AccountMemberFactory> */
    use HasFactory;

    protected $casts = [
        'role' => AccountRole::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
