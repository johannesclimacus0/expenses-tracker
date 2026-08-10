<?php

namespace App\Actions\Auth;

use App\Actions\Accounts\CreatePersonalAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RegisterUser
{
    public function __construct(private CreatePersonalAccount $createPersonalAccount) {}

    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create($data);

            $this->createPersonalAccount->handle($user);

            return $user;
        });
    }
}
