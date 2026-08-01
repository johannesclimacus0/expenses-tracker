<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendPasswordResetEmailRequest;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function index()
    {
        return view('auth.forgot-password');
    }

    public function store(SendPasswordResetEmailRequest $request)
    {
        $email = $request->safe()->only(['email']);
        $retryAfter = (int) config('auth.passwords.users.throttle', 60);

        Password::sendResetLink($email);

        $request->session()->put(
            'password_reset_retry_until',
            now()->addSeconds($retryAfter)->timestamp,
        );

        return back()
            ->with('status', 'Если аккаунт существует, ссылка для сброса пароля была отправлена')
            ->withInput($request->only('email'));
    }
}
