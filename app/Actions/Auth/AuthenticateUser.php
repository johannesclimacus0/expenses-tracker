<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class AuthenticateUser
{
    public function handle(array $data, bool $remember = false): bool
    {
        return Auth::attempt($data, $remember);
    }
}
