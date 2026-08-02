<?php

namespace App\Http\Controllers\Transactions;

use App\Actions\Transactions\CreateTransaction;
use App\Actions\Transactions\DeleteTransaction;
use App\Actions\Transactions\ExportTransactionsCsv;
use App\Actions\Transactions\ImportTransactionsCsv;
use App\Actions\Transactions\ListTransactions;
use App\Actions\Transactions\UpdateTransaction;
use App\DTOs\Transactions\TransactionData;
use App\DTOs\Transactions\TransactionFiltersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\DeleteTransactionRequest;
use App\Http\Requests\Transactions\FilterTransactionsRequest;
use App\Http\Requests\Transactions\ImportTransactionsCsvRequest;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function importCsv(ImportTransactionsCsvRequest $request, ImportTransactionsCsv $importTransactions): RedirectResponse
    {
        $file = $request->file('csv');

        if (! $file instanceof UploadedFile) {
            return back()->withErrors(['csv' => 'Выберите CSV-файл.']);
        }

        $count = $importTransactions->handle($request->user(), $file);

        return to_route('transactions.index')
            ->with('status', "Импортировано транзакций: {$count}");
    }

    public function exportCsv(Request $request, ExportTransactionsCsv $exportTransactions): StreamedResponse
    {
        Gate::authorize('viewAny', Transaction::class);
        $user = $request->user();

        return response()->streamDownload(
            function () use ($exportTransactions, $user): void {
                $stream = fopen('php://output', 'wb');

                if ($stream === false) {
                    throw new RuntimeException('Ошибка при записи.');
                }

                try {
                    $exportTransactions->handle($user, $stream);
                } finally {
                    fclose($stream);
                }
            },
            'transactions-'.now()->format('Y-m-d').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-16LE'],
        );
    }
}
