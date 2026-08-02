<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\DashboardPeriod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['currency', 'dashboard_period', 'transactions_per_page', 'budget_warning_percent', 'show_cents',])]
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
}
