<x-layouts.guest-layout title="Новый пароль">
    <x-auth.header title="Создайте новый пароль"/>

    <p class="-mt-4 mb-6 text-xs leading-5 text-slate-500">
        Укажите новый пароль
    </p>

    <x-forms.form-error key="token"/>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ old('token', $token ?? request()->route('token')) }}">
        <div>
            <label for="email" class="mb-1.5 block text-xs font-medium text-slate-600">Email<span class="text-red-500"> *</span></label>
            <input id="email" name="email" type="email" value="{{ old('email', $email ?? request()->query('email')) }}" autocomplete="email" required autofocus
                @class([
                    'block h-10 w-full rounded-xl border bg-stone-50 px-3.5 text-xs text-slate-900 outline-none transition placeholder:text-slate-400',
                    'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' => $errors->has('email'),
                    'border-transparent hover:border-slate-300 focus:border-slate-500 focus:bg-white' => ! $errors->has('email'),
                ])
            >
            @error('email')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <x-forms.input
            name="password"
            label="Новый пароль"
            type="password"
            autocomplete="new-password"
            required
        />

        <x-forms.input
            name="password_confirmation"
            label="Повторите пароль"
            type="password"
            autocomplete="new-password"
            required
        />

        <x-buttons.primary-button type="submit">
            Сохранить новый пароль
        </x-buttons.primary-button>
    </form>
    <x-auth.footer
        text="Вспомнили пароль?"
        :link="route('login')"
        link-text="Вернуться ко входу"
    />
</x-layouts.guest-layout>
