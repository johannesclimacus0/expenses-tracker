@props([
    'paginator',
])

@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-slate-100 px-5 py-4">
        @if ($paginator->onFirstPage())
            <span class="text-xs text-slate-300">Назад</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="text-xs font-medium text-slate-500 transition hover:text-slate-900">Назад</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="text-xs font-medium text-slate-500 transition hover:text-slate-900">Далее</a>
        @else
            <span class="text-xs text-slate-300">Далее</span>
        @endif
    </nav>
@endif
