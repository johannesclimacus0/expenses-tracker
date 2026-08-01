<?php

namespace App\Actions\Categories;

use App\DTOs\Categories\CategoryData;
use App\Models\Category;
use App\Models\User;

final class CreateCategory
{
    public function handle(User $user, CategoryData $data): Category
    {
        return $user->categories()->create([
            'name' => $data->name,
            'type' => $data->type,
        ]);
    }
}
