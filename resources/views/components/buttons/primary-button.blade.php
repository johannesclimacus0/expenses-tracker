<button
    {{ $attributes->class([
        'h-10 w-full rounded-xl bg-slate-900 px-4',
        'text-xs font-medium text-white shadow-sm',
        'transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50',
    ]) }}
>
    {{$slot}}
</button>
