<?php

namespace App\Http\Controllers\Transactions;

use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transactions\DeleteTransaction;
use App\Actions\Transactions\ListTransactions;
use App\Actions\Transactions\UpdateTransaction;
use App\DTOs\Transactions\TransactionData;
use App\DTOs\Transactions\TransactionFiltersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\DeleteTransactionRequest;
use App\Http\Requests\Transactions\FilterTransactionsRequest;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(FilterTransactionsRequest $request, ListTransactions $listTransactions): View
    {
        $filters = TransactionFiltersData::fromArray($request->validated());
        $transactions = $listTransactions->handle($request->user(), $filters);

        $categories = $request->user()->categories()
            ->orderBy('name')
            ->get();

        return view('transactions.index', compact('transactions', 'categories'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Transaction::class);

        $categories = $request->user()->categories()
            ->orderBy('name')
            ->get();

        return view('transactions.create', compact('categories'));
    }

    public function store(StoreTransactionRequest $request, CreateTransaction $createTransaction): RedirectResponse
    {
        $data = TransactionData::fromArray($request->validated());

        $createTransaction->handle($request->user(), $data);

        return to_route('transactions.index')->with('status', 'Транзакция добавлена');
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        Gate::authorize('update', $transaction);

        $categories = $request->user()->categories()
            ->orderBy('name')
            ->get();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction, UpdateTransaction $updateTransaction): RedirectResponse
    {
        $data = TransactionData::fromArray($request->validated());

        $updateTransaction->handle($transaction, $data);

        return to_route('transactions.index')->with('status', 'Транзакция обновлена');
    }

    public function destroy(DeleteTransactionRequest $request, Transaction $transaction, DeleteTransaction $deleteTransaction): RedirectResponse
    {
        $deleteTransaction->handle($transaction);

        return to_route('transactions.index')->with('status', 'Транзакция удалена');
    }
}
