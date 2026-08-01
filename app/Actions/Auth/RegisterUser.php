<?php

namespace App\Actions\Auth;

use App\Models\User;

class RegisterUser
{
    public function handle(array $data): User
    {
        return User::query()->create($data);
    }
}
