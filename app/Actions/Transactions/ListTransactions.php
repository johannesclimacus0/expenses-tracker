<?php

namespace App\Actions\Transactions;

use App\DTOs\Transactions\TransactionFiltersData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTransactions
{
    public function handle(User $user, TransactionFiltersData $filters): LengthAwarePaginator
    {
        $query = $user->transactions()->with('category');
        if ($filters->type !== null) {
            $query->where('type', $filters->type->value);
        }

        if ($filters->categoryUuid !== null) {
            $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('uuid', $filters->categoryUuid),
            );
        }

        if ($filters->from !== null) {
            $query->where('occurred_at', '>=', $filters->from);
        }

        if ($filters->to !== null) {
            $query->where('occurred_at', '<=', $filters->to);
        }

        return $query
            ->orderByDesc('occurred_at')
            ->paginate($user->settings->transactions_per_page)
            ->withQueryString();
    }
}
