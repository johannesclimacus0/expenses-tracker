<?php

namespace App\Http\Controllers\Categories;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\Actions\Categories\CreateCategory;
use App\Actions\Categories\DeleteCategory;
use App\Actions\Categories\UpdateCategory;
use App\DTOs\Categories\CategoryData;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\DeleteCategoryRequest;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request, ResolveCurrentAccount $resolveCurrentAccount): View
    {
        Gate::authorize('viewAny', Category::class);

        $account = $resolveCurrentAccount->handle($request->user());

        $expenseCategories = $account->categories()
            ->where('type', TransactionType::Expense->value)
            ->orderBy('name')
            ->simplePaginate(perPage: 10, pageName: 'expenses_page')
            ->withQueryString();

        $incomeCategories = $account->categories()
            ->where('type', TransactionType::Income->value)
            ->orderBy('name')
            ->simplePaginate(perPage: 10, pageName: 'income_page')
            ->withQueryString();

        return view('categories.index', compact('expenseCategories', 'incomeCategories'));
    }

    public function store(StoreCategoryRequest $request, CreateCategory $createCategory): RedirectResponse
    {
        $data = CategoryData::fromArray($request->validated());

        $createCategory->handle($request->user(), $data);

        return to_route('categories.index')->with('status', 'Категория добавлена');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.edit', ['category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategory $updateCategory): RedirectResponse
    {
        $data = CategoryData::fromArray($request->validated());

        $updateCategory->handle($category, $data);

        return to_route('categories.index')->with('status', 'Категория обновлена');
    }

    public function destroy(DeleteCategoryRequest $request, Category $category, DeleteCategory $deleteCategory): RedirectResponse
    {
        $deleteCategory->handle($category);

        return to_route('categories.index')->with('status', 'Категория удалена');
    }
}
