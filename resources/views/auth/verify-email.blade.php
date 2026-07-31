<x-layouts.app-layout title="Подтверждение email">
    @php
        $retryUntil = (int) session('verification_retry_until', 0);
        $retryAfter = max(0, $retryUntil - now()->timestamp);
        $isThrottled = $retryAfter > 0;
    @endphp

    <div class="flex min-h-[calc(100vh-7rem)] items-center justify-center">
        <section class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-5 shadow-lg sm:p-6">
            <header class="mb-5">
                <h1 class="text-xl font-semibold tracking-tight text-slate-900">Подтвердите email</h1>
                <p class="mt-2 text-xs leading-5 text-slate-500">
                    Мы отправили ссылку для подтверждения на
                    <span class="font-medium text-slate-700">{{ auth()->user()->email }}</span>.
                    Откройте письмо и перейдите по ссылке
                </p>
            </header>

            @if ($isThrottled)
                <x-auth.cooldown
                    :retry-after="$retryAfter"
                    message="Слишком много попыток. Повторная отправка будет доступна через"
                />
            @elseif (session('status') === 'verification-link-sent')
                <div role="status" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs text-emerald-700">
                    Новая ссылка отправлена. Проверьте входящие сообщения и папку «Спам»
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-buttons.primary-button
                    type="submit"
                    :disabled="$isThrottled"
                    data-throttle-submit
                    data-retry-after="{{ $retryAfter }}"
                >
                    Отправить ссылку ещё раз
                </x-buttons.primary-button>
            </form>

            <p class="mt-4 text-center text-xs text-slate-400">
                Письмо может прийти в течение нескольких минут
            </p>
        </section>
    </div>
</x-layouts.app-layout>
