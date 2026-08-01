<?php

namespace App\Actions\Categories;

use App\Models\Category;

final class DeleteCategory
{
    public function handle(Category $category): void
    {
        $category->delete();
    }
}
