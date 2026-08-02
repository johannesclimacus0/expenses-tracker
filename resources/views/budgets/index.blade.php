<x-layouts.app-layout title="Бюджеты">
    <div class="mx-auto max-w-4xl">
        <header class="mb-5 flex items-center gap-4">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Бюджеты</h1>
            <span class="h-px flex-1 bg-slate-200"></span>
            <a href="{{ route('budgets.create', ['month' => $selectedMonth->format('Y-m')]) }}" title="Новый бюджет" class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-lg font-light leading-none text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white hover:ring-slate-900">
                <span>+</span>
            </a>
        </header>

        <x-auth.status :message="session('status')" />
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" action="{{ route('budgets.index') }}" class="mb-4 flex items-end gap-3 border-y border-slate-200 py-3">
            <div>
                <label for="month" class=" mb-1 block text-xs text-slate-400 ">Месяц</label>
                <input id="month" name="month" type="month" value="{{ $selectedMonth->format('Y-m') }}" class="cursor-pointer block h-8 border-0 border-b border-slate-200 bg-transparent px-0 text-xs text-slate-700 outline-none focus:border-slate-500 focus:ring-0">
            </div>
            <button type="submit" class="cursor-pointer mb-1 text-xs font-medium text-slate-900 underline decoration-slate-300 underline-offset-4">
                Показать
            </button>
        </form>

        <section class="rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200">
            @forelse ($usage as $item)
                <x-budgets.budget-row :item="$item" />
            @empty
                <div class="px-5 py-14 text-center">
                    <p class="text-sm font-medium text-slate-700">Бюджетов пока нет</p>
                </div>
            @endforelse
        </section>
    </div>
</x-layouts.app-layout>
