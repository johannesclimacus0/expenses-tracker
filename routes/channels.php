<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('accounts.{accountUuid}', function (User $user, string $accountUuid): bool {
    return $user->accounts()
        ->where('accounts.uuid', $accountUuid)
        ->exists();
});
