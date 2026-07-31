<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function () {
            if($this->app->isLocal()){
                return Password::min(8);
            }

            return Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised();
        });

        RateLimiter::for('verification-email',
            fn (Request $request) => Limit::perMinute(1)
                ->by($request->user()->getAuthIdentifier())
                ->response(function (Request $request, array $headers) {
                    $retryAfter = (int) ($headers['Retry-After'] ?? 60);

                    $request->session()->put(
                        'verification_retry_until',
                        now()->addSeconds($retryAfter)->timestamp,
                    );

                    return back()
                        ->withHeaders($headers)
                        ->with('status', 'verification-throttled')
                        ->with('retry_after', $retryAfter);
                })
        );

        $loginRateLimitResponse = function (Request $request, array $headers) {
            return back()
                ->withHeaders($headers)
                ->withErrors([
                    'auth' => 'Слишком много попыток входа. Попробуйте позже.',
                ])
                ->withInput($request->only('email', 'remember'));
        };

        RateLimiter::for('login', function (Request $request) use ($loginRateLimitResponse){
            return[
                Limit::perMinute(20)->by($request->ip())->response($loginRateLimitResponse),
                Limit::perMinute(10)->by($request->post('email'))->response($loginRateLimitResponse),
            ];
        });

        $passwordResetRateLimitResponse = function (Request $request, array $headers) {
            $retryAfter = (int) ($headers['Retry-After'] ?? 60);

            $request->session()->put(
                'password_reset_retry_until',
                now()->addSeconds($retryAfter)->timestamp,
            );

            return back()
                ->withHeaders($headers)
                ->with('status', 'Если аккаунт существует, ссылка для сброса пароля была отправлена.')
                ->withInput($request->only('email'));
        };

        RateLimiter::for('password-reset-email', function (Request $request) use ($passwordResetRateLimitResponse) {
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(10)
                    ->by('ip:'.$request->ip())
                    ->response($passwordResetRateLimitResponse),
                Limit::perMinute(1)
                    ->by('email:'.$email)
                    ->response($passwordResetRateLimitResponse),
            ];
        });

        Model::shouldBeStrict(! $this->app->isProduction());

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(app()->isProduction());
    }
}
