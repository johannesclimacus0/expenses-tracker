<x-layouts.guest-layout title="Восстановление пароля">
    @php
        $retryUntil = (int) session('password_reset_retry_until', 0);
        $retryAfter = max(0, $retryUntil - now()->timestamp);
        $isThrottled = $retryAfter > 0;
    @endphp
    <x-auth.header title="Восстановление пароля"/>

    <p class="-mt-4 mb-6 text-xs leading-5 text-slate-500">
        Отправим ссылку для восстановления на указанную почту
    </p>
    @if ($isThrottled)
        <x-auth.cooldown
            :retry-after="$retryAfter"
            message="Ссылка отправлена. Повторный запрос будет доступен через"
        />
    @else
        <x-auth.status :message="session('status')"/>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-forms.input
            name="email"
            label="Email"
            type="email"
            autocomplete="email"
            autofocus
            required
        />

        <x-buttons.primary-button
            type="submit"
            :disabled="$isThrottled"
            data-throttle-submit
            data-retry-after="{{ $retryAfter }}"
        >
            Отправить ссылку
        </x-buttons.primary-button>
    </form>
    <x-auth.footer
        text="Вспомнили пароль?"
        :link="route('login')"
        link-text="Вернуться ко входу"
    />
</x-layouts.guest-layout>
