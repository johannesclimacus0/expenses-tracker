<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $active_account_id
 * @property Currency $currency
 * @property DashboardPeriod $dashboard_period
 * @property int $transactions_per_page
 * @property int $budget_warning_percent
 * @property bool $show_cents
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Account|null $activeAccount
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereActiveAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereBudgetWarningPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereDashboardPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereShowCents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereTransactionsPerPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['active_account_id', 'currency', 'dashboard_period', 'transactions_per_page', 'budget_warning_percent', 'show_cents'])]
class UserSetting extends Model
{
    protected function casts(): array
    {
        return [
            'currency' => Currency::class,
            'dashboard_period' => DashboardPeriod::class,
            'transactions_per_page' => 'integer',
            'budget_warning_percent' => 'integer',
            'show_cents' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activeAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'active_account_id');
    }
}
