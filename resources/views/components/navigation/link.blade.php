@props([
    'href',
    'active' => false,
])

<a href="{{$href}}"
    {{ $attributes->class([
        'relative flex items-center rounded-full px-3 py-1.5 text-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900',
        'bg-slate-900 font-medium text-white' => $active,
        'text-slate-500 hover:bg-stone-100 hover:text-slate-900' => ! $active,
    ]) }}
>
    {{$slot}}
</a>
