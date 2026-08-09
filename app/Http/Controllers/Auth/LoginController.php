<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuthenticateUser $auth)
    {
        $data = $request->safe()->only(['email', 'password']);
        $remember = $request->boolean('remember');

        if (!$auth->handle($data, $remember)) {
            return back()
                ->withErrors([
                    'auth' => 'Неверная почта или пароль',
                ])
                ->onlyInput('email', 'remember');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy()
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('login');
    }
}
