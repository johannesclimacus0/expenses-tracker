<?php

namespace App\Http\Requests\Accounts;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\AccountMember;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->route('account');
        $member = $this->route('member');

        return $account instanceof Account
            && $member instanceof AccountMember
            && $member->account_id === $account->getKey()
            && $member->role === AccountRole::Member
            && ($this->user()?->can('manageMembers', $account) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
