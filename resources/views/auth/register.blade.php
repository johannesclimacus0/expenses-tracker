<x-layouts.guest-layout title="Регистрация">
    <x-auth.header title="Создание аккаунта"/>
    <x-forms.form-error />
    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <x-forms.input
            name="name"
            label="Имя"
            autocomplete="name"
            autofocus
            required
        />

        <x-forms.input
            name="email"
            label="Email"
            type="email"
            autocomplete="email"
            required
        />

        <x-forms.input
            name="password"
            label="Пароль"
            type="password"
            autocomplete="new-password"
            required
        />

        <x-forms.input
            name="password_confirmation"
            label="Подтверждение пароля"
            type="password"
            autocomplete="new-password"
            required
        />

        <x-buttons.primary-button type="submit">
            Зарегистрироваться
        </x-buttons.primary-button>
    </form>

    <x-auth.footer
        text="Уже есть аккаунт?"
        :link="route('login')"
        link-text="Войти"
    />
</x-layouts.guest-layout>
