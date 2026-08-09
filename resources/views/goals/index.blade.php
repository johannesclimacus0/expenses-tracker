<x-layouts.app-layout title="Финансовые цели">
    <div class="mx-auto max-w-4xl">
        <header class="mb-5 flex items-center gap-4">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Цели</h1>
            <span class="h-px flex-1 bg-slate-200"></span>
            <a href="{{ route('goals.create') }}" title="Новая цель" class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-lg font-light leading-none text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white hover:ring-slate-900"><span>+</span></a>
        </header>

        <x-auth.status :message="session('status')" />
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">{{ $errors->first() }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
            @forelse ($goals as $goal)
                <x-goals.goal-row :goal="$goal" :progress="$progressByGoal[$goal->id]" />
            @empty
                <div class="px-5 py-16 text-center">
                    <p class="text-sm font-medium text-slate-700">Целей пока нет</p>
                    <a href="{{ route('goals.create') }}" class="mt-3 inline-block text-xs text-slate-400 transition hover:text-slate-900">Создать первую</a>
                </div>
            @endforelse
            <x-pagination.simple :paginator="$goals" />
        </section>
    </div>
</x-layouts.app-layout>
