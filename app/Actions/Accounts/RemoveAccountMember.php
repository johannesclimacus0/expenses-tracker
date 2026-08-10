<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Models\AccountMember;
use App\Models\UserSetting;
use Illuminate\Support\Facades\DB;

final class RemoveAccountMember
{
    public function handle(AccountMember $member): void
    {
        DB::transaction(function () use ($member): void {
            UserSetting::query()
                ->where('user_id', $member->user_id)
                ->where('active_account_id', $member->account_id)
                ->update(['active_account_id' => null]);

            $member->delete();
        });
    }
}
