<x-layouts.app-layout title="Новая цель">
    <div class="mx-auto max-w-md">
        <a href="{{ route('goals.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900">Цели</a>
        <header class="mb-5 mt-3"><h1 class="text-lg font-semibold tracking-tight text-slate-900">Новая цель</h1></header>
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <x-goals.goal-form :action="route('goals.store')" submit-label="Создать" />
        </section>
    </div>
</x-layouts.app-layout>
