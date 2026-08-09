@props([
    'paginator',
    'compact' => false,
])

@if ($paginator->hasPages())
    <nav {{ $attributes->class([
        'flex items-center justify-between border-t border-slate-100',
        'px-5 py-4' => ! $compact,
        'mt-5 pt-4' => $compact,
    ]) }}>
        @if ($paginator->onFirstPage())
            <span class="text-xs text-slate-300">Назад</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="text-xs font-medium text-slate-500 transition hover:text-slate-900">Назад</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="text-xs font-medium text-slate-500 transition hover:text-slate-900">Далее</a>
        @else
            <span class="text-xs text-slate-300">Далее</span>
        @endif
    </nav>
@endif
