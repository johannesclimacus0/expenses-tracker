<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    public function create(Request $request)
    {
        $token = $request->route('token');
        $email = $request->query('email');

        return view('auth.reset-password', compact('token', 'email'));
    }

    public function store(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $status = Password::reset($data, function (User $user, #[\SensitiveParameter] string $password) {
            $user
                ->forceFill(['password' => $password])
                ->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Пароль успешно изменён. Теперь вы можете войти');
        }

        return back()
            ->withErrors([
                'email' => 'Не удалось изменить пароль. Запросите новую ссылку для сброса',
            ])
            ->onlyInput('email');
    }
}
