<x-settings.form-section
    :action="route('settings.password.update')"
    title="Безопасность"
    description="Смена пароля"
    status="password-updated"
    submit-label="Обновить пароль"
>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-forms.input name="current_password" label="Текущий пароль" type="password" autocomplete="current-password" required />
        <x-forms.input name="password" label="Новый пароль" type="password" autocomplete="new-password" required />
        <x-forms.input name="password_confirmation" label="Повторите пароль" type="password" autocomplete="new-password" required />
    </div>
</x-settings.form-section>
