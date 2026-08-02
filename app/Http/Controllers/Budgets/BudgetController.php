<?php

namespace App\Http\Controllers\Budgets;

use App\Actions\Budgets\CreateBudget;
use App\Actions\Budgets\DeleteBudget;
use App\Actions\Budgets\UpdateBudget;
use App\DTOs\Budgets\BudgetData;
use App\DTOs\Budgets\BudgetMonthData;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budgets\DeleteBudgetRequest;
use App\Http\Requests\Budgets\FilterBudgetsRequest;
use App\Http\Requests\Budgets\StoreBudgetRequest;
use App\Http\Requests\Budgets\UpdateBudgetRequest;
use App\Models\Budget;
use App\Services\Budgets\BudgetUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(FilterBudgetsRequest $request, BudgetUsageService $budgetUsageService): View
    {
        $monthData = BudgetMonthData::fromArray($request->validated());
        $usage = $budgetUsageService->forMonth($request->user(), $monthData->month);

        return view('budgets.index', [
            'usage' => $usage,
            'selectedMonth' => $monthData->month,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Budget::class);

        return view('budgets.create', [
            'categories' => $this->expenseCategories($request),
        ]);
    }

    public function store(StoreBudgetRequest $request, CreateBudget $createBudget): RedirectResponse
    {
        $data = BudgetData::fromArray($request->validated());
        $createBudget->handle($request->user(), $data);

        return to_route('budgets.index', [
            'month' => $data->month->format('Y-m'),
        ])->with('status', 'Бюджет добавлен');
    }

    public function edit(Request $request, Budget $budget): View
    {
        Gate::authorize('update', $budget);

        return view('budgets.edit', [
            'budget' => $budget,
            'categories' => $this->expenseCategories($request),
        ]);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget, UpdateBudget $updateBudget): RedirectResponse
    {
        $data = BudgetData::fromArray($request->validated());
        $updateBudget->handle($budget, $data);

        return to_route('budgets.index', [
            'month' => $data->month->format('Y-m'),
        ])->with('status', 'Бюджет обновлён');
    }

    public function destroy(DeleteBudgetRequest $request, Budget $budget, DeleteBudget $deleteBudget): RedirectResponse
    {
        $month = $budget->month->format('Y-m');
        $deleteBudget->handle($budget);

        return to_route('budgets.index', [
            'month' => $month,
        ])->with('status', 'Бюджет удалён');
    }

    private function expenseCategories(Request $request)
    {
        return $request->user()->categories()
            ->where('type', TransactionType::Expense->value)
            ->orderBy('name')
            ->get();
    }
}
