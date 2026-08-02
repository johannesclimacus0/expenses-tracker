<?php

namespace App\Actions\Settings;

use App\DTOs\Settings\PasswordData;
use App\Models\User;
use Illuminate\Support\Str;

final class UpdatePassword
{
    public function handle(User $user, #[\SensitiveParameter] PasswordData $data): void
    {
        $user->forceFill([
            'password' => $data->password,
            'remember_token' => Str::random(60),
        ])->save();
    }
}
