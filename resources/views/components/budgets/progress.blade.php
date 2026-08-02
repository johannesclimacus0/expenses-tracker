@props(['item'])

@php
    $progress = min(100, max(0, $item->percentage));
    $danger = $item->warning || $item->exceeded;
@endphp

<div {{ $attributes->class('h-1.5 w-full overflow-hidden rounded-full bg-slate-200') }}>
    <div
        @class([
            'h-full rounded-full',
            'bg-red-500' => $danger,
            'bg-yellow-500' => ! $danger && $item->percentage > 50,
            'bg-emerald-400' => ! $danger && $item->percentage <= 50,
        ])
        style="width: {{ $progress }}%"
    ></div>
</div>
