<x-layouts.app-layout title="Настройки">
    <div class="mx-auto max-w-5xl">
        <header class="mb-5 flex items-center gap-4">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Настройки</h1>
            <span class="h-px flex-1 bg-slate-200"></span>
            <a href="{{ route('dashboard') }}" class="text-xs text-slate-400 transition hover:text-slate-900">Обзор</a>
        </header>

        <div class="space-y-4">
            <x-settings.profile-form :user="auth()->user()" />
            <x-settings.interface-form
                :settings="$settings"
                :currencies="$currencies"
                :dashboard-periods="$dashboardPeriods"
            />
            <x-settings.password-form />
        </div>
    </div>
</x-layouts.app-layout>
