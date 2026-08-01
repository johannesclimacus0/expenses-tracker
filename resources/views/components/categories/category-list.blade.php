@props([
    'categories',
    'title',
    'type',
])

@php
    $dotClass = match ($type) {
        'expense' => 'bg-rose-400',
        'income' => 'bg-emerald-400',
    };
@endphp

<section {{ $attributes->class(['flex flex-col p-5 sm:p-6']) }}>
    <div class="mb-4 flex items-center gap-2">
        <span @class(['size-2 rounded-full', $dotClass])></span>
        <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
    </div>

    <div class="flex-1 divide-y divide-slate-100">
        @forelse ($categories as $category)
            <div class="flex items-center gap-3 py-3 transition hover:bg-stone-50 first:pt-0 last:pb-0">
                <span class="min-w-0 flex-1 truncate text-xs font-medium text-slate-700">
                    {{ $category->name }}
                </span>
                <a href="{{ route('categories.edit', $category) }}"
                    class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                >
                Изменить
                </a>
                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Удалить категорию?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-slate-300 transition hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                        Удалить
                    </button>
                </form>
            </div>
        @empty
            <p class="py-8 text-center text-xs text-slate-400">Здесь пока пусто</p>
        @endforelse
    </div>

    @if ($categories->hasPages())
        <nav class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
            @if ($categories->onFirstPage())
                <span class="text-xs text-slate-300">Назад</span>
            @else
                <a href="{{ $categories->previousPageUrl() }}" rel="prev"
                    class="text-xs font-medium text-slate-500 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                >
                Назад
                </a>
            @endif

            @if ($categories->hasMorePages())
                <a href="{{ $categories->nextPageUrl() }}" rel="next"
                    class="text-xs font-medium text-slate-500 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900"
                >
                Далее
                </a>
            @else
                <span class="text-xs text-slate-300">Далее</span>
            @endif
        </nav>
    @endif
</section>
