<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('telegraph.webhook.secret');

        if ($secret !== '') {
            $providedSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token');

            abort_unless(hash_equals($secret, $providedSecret), Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
