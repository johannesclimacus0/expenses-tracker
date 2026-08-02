<?php

namespace App\Actions\Settings;

use App\DTOs\Settings\ProfileData;
use App\Models\User;

final class UpdateProfile
{
    public function handle(User $user, ProfileData $data): bool
    {
        $user->fill([
            'name' => $data->name,
            'email' => $data->email,
        ]);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return $emailChanged;
    }
}
