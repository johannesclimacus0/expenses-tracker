<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrentAccountController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'account_uuid' => ['required', Rule::exists('accounts', 'uuid')],
        ]);
        $account = Account::query()
            ->where('uuid', $validated['account_uuid'])
            ->firstOrFail();

        abort_unless(
            $user->accounts()->whereKey($account->id)->exists(),
            403,
        );

        $user->settings()->updateOrCreate(
            [],
            ['active_account_id' => $account->id],
        );

        return back()->with('status', 'Счет переключен');
    }
}
