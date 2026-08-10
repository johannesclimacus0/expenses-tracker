<?php

namespace App\Http\Requests\Accounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StoreAccountMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->route('account');

        return $account instanceof Account
            && ($this->user()?->can('manageMembers', $account) ?? false);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('email')) {
                    return;
                }

                $account = $this->route('account');
                $user = User::query()
                    ->whereRaw('LOWER(email) = ?', [$this->string('email')->toString()])
                    ->first();

                if ($user === null) {
                    $validator->errors()->add('email', 'Пользователь с такой почтой не найден');

                    return;
                }

                if ($account instanceof Account
                    && $account->members()->where('user_id', $user->getKey())->exists()) {
                    $validator->errors()->add('email', 'Пользователь уже добавлен');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Введите почту пользователя',
            'email.email' => 'Введите корректную почту',
        ];
    }
}
