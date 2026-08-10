<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounts;

use App\Actions\Accounts\AddAccountMember;
use App\Actions\Accounts\RemoveAccountMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\DeleteAccountMemberRequest;
use App\Http\Requests\Accounts\StoreAccountMemberRequest;
use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AccountMemberController extends Controller
{
    public function store(StoreAccountMemberRequest $request, Account $account, AddAccountMember $action): RedirectResponse
    {
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$request->validated('email')])
            ->firstOrFail();
        $action->handle($account, $user);

        return to_route('accounts.edit', $account)->with('status', 'Участник добавлен');
    }

    public function destroy(
        DeleteAccountMemberRequest $request,
        Account $account,
        AccountMember $member,
        RemoveAccountMember $action,
    ): RedirectResponse {
        $action->handle($member);

        return to_route('accounts.edit', $account)->with('status', 'Участник удален');
    }
}
