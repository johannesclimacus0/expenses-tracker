@props(['user'])

<x-settings.form-section
    :action="route('settings.profile.update')"
    title="Профиль"
    description="Имя и адрес для входа"
    status="profile-updated"
    submit-label="Сохранить профиль"
>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-forms.input name="name" label="Имя" :value="$user->name" autocomplete="name" required />
        <x-forms.input name="email" label="Почта" type="email" :value="$user->email" autocomplete="email" required />
    </div>
</x-settings.form-section>
