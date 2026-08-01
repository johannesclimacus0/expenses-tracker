<?php

namespace App\Actions\Categories;

use App\DTOs\Categories\CategoryData;
use App\Models\Category;

final class UpdateCategory
{
    public function handle(Category $category, CategoryData $data): Category
    {
        $category->update([
            'name' => $data->name,
            'type' => $data->type,
        ]);

        return $category->refresh();
    }
}
