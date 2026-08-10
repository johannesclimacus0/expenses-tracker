<?php

declare(strict_types=1);

namespace App\Http\Controllers\RecurringTransactions;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\Actions\RecurringTransactions\CreateRecurringTransaction;
use App\Actions\RecurringTransactions\DeleteRecurringTransaction;
use App\Actions\RecurringTransactions\UpdateRecurringTransaction;
use App\DTOs\RecurringTransactions\RecurringTransactionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecurringTransactions\DeleteRecurringTransactionRequest;
use App\Http\Requests\RecurringTransactions\StoreRecurringTransactionRequest;
use App\Http\Requests\RecurringTransactions\UpdateRecurringTransactionRequest;
use App\Models\RecurringTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    public function index(Request $request, ResolveCurrentAccount $resolveCurrentAccount): View
    {
        Gate::authorize('viewAny', RecurringTransaction::class);

        $account = $resolveCurrentAccount->handle($request->user());

        $recurringTransactions = $request->user()
            ->recurringTransactions()
            ->where('account_id', $account->id)
            ->with('category')
            ->orderByDesc('is_active')
            ->orderBy('next_run_at')
            ->paginate(10)
            ->withQueryString();

        return view('recurring-transactions.index', compact('recurringTransactions'));
    }

    public function create(Request $request, ResolveCurrentAccount $resolveCurrentAccount): View
    {
        Gate::authorize('create', RecurringTransaction::class);

        $account = $resolveCurrentAccount->handle($request->user());

        $categories = $request->user()->categories()
            ->where('account_id', $account->id)
            ->orderBy('name')
            ->get();

        return view('recurring-transactions.create', compact('categories'));
    }

    public function store(
        StoreRecurringTransactionRequest $request,
        CreateRecurringTransaction $createRecurringTransaction,
    ): RedirectResponse {
        $data = RecurringTransactionData::fromArray($request->validated());

        $createRecurringTransaction->handle($request->user(), $data);

        return to_route('recurring-transactions.index')
            ->with('status', 'Регулярная транзакция добавлена');
    }

    public function edit(
        Request $request,
        RecurringTransaction $recurringTransaction,
        ResolveCurrentAccount $resolveCurrentAccount,
    ): View {
        Gate::authorize('update', $recurringTransaction);

        $account = $resolveCurrentAccount->handle($request->user());

        $categories = $request->user()->categories()
            ->where('account_id', $account->id)
            ->orderBy('name')
            ->get();

        return view('recurring-transactions.edit', compact('recurringTransaction', 'categories'));
    }

    public function update(
        UpdateRecurringTransactionRequest $request,
        RecurringTransaction $recurringTransaction,
        UpdateRecurringTransaction $updateRecurringTransaction,
    ): RedirectResponse {
        $data = RecurringTransactionData::fromArray($request->validated());

        $updateRecurringTransaction->handle($recurringTransaction, $data);

        return to_route('recurring-transactions.index')
            ->with('status', 'Регулярная транзакция обновлена');
    }

    public function destroy(
        DeleteRecurringTransactionRequest $request,
        RecurringTransaction $recurringTransaction,
        DeleteRecurringTransaction $deleteRecurringTransaction,
    ): RedirectResponse {
        $deleteRecurringTransaction->handle($recurringTransaction);

        return to_route('recurring-transactions.index')
            ->with('status', 'Регулярная транзакция удалена');
    }
}
