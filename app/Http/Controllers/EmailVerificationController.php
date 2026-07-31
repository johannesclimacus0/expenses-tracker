<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class EmailVerificationController extends Controller
{
    public function __invoke(EmailVerificationRequest $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        $request->fulfill();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
