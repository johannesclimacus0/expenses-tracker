<x-layouts.guest-layout title="Вход">
    <x-auth.header title="Вход в аккаунт"/>

    <x-auth.status :message="session('status')"/>
    <x-forms.form-error />
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <x-forms.input
            name="email"
            label="Email"
            type="email"
            autocomplete="email"
            autofocus
            required
        />

        <x-forms.input
            name="password"
            label="Пароль"
            type="password"
            autocomplete="current-password"
            required
        >
            <x-slot:action>
                <a href="{{ route('password.request') }}" class="text-xs text-slate-500 underline-offset-4 hover:text-slate-900 hover:underline">
                    Забыли пароль?
                </a>
            </x-slot:action>
        </x-forms.input>

        <x-forms.checkbox
            name="remember"
            label="Запомнить меня"
        />

        <x-buttons.primary-button type="submit">
            Войти
        </x-buttons.primary-button>
    </form>

    <x-auth.footer
        text="Нет аккаунта?"
        :link="route('register')"
        link-text="Зарегистрироваться"
    />
</x-layouts.guest-layout>
