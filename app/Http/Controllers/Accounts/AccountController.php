<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounts;

use App\Actions\Accounts\CreateAccount;
use App\Actions\Accounts\DeleteAccount;
use App\Actions\Accounts\UpdateAccount;
use App\DTOs\Accounts\AccountData;
use App\Enums\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\DeleteAccountRequest;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Account::class);

        $accounts = $request->user()->accounts()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('accounts.index', ['accounts' => $accounts, 'currencies' => Currency::cases()]);
    }

    public function store(StoreAccountRequest $request, CreateAccount $action): RedirectResponse
    {
        $account = $action->handle($request->user(), AccountData::fromArray($request->validated()));
        $request->user()->settings()->updateOrCreate([], ['active_account_id' => $account->getKey()]);

        return to_route('accounts.index')->with('status', 'Счет создан');
    }

    public function edit(Account $account): View
    {
        Gate::authorize('update', $account);

        $account->load(['members.user' => fn ($query) => $query->orderBy('name')]);

        return view('accounts.edit', ['account' => $account, 'currencies' => Currency::cases()]);
    }

    public function update(UpdateAccountRequest $request, Account $account, UpdateAccount $action): RedirectResponse
    {
        $action->handle($account, AccountData::fromArray($request->validated()));

        return to_route('accounts.index')->with('status', 'Счет обновлен');
    }

    public function destroy(DeleteAccountRequest $request, Account $account, DeleteAccount $action): RedirectResponse
    {
        $action->handle($request->user(), $account);

        return to_route('accounts.index')->with('status', 'Счет удален');
    }
}
