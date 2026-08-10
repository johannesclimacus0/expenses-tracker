<?php

namespace App\Actions\Categories;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\DTOs\Categories\CategoryData;
use App\Models\Category;
use App\Models\User;

final readonly class CreateCategory
{
    public function __construct(private ResolveCurrentAccount $accounts) {}

    public function handle(User $user, CategoryData $data): Category
    {
        $account = $this->accounts->handle($user);

        return $user->categories()->create([
            'account_id' => $account->getKey(),
            'name' => $data->name,
            'type' => $data->type,
        ]);
    }
}
