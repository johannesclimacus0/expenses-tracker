@props(['progress'])

@php
    $width = min(100, max(0, $progress->percentage));
@endphp

<div {{ $attributes->class(['h-1.5 w-full overflow-hidden rounded-full bg-slate-200']) }}>
    <div
        @class([
            'h-full rounded-full',
            'bg-emerald-300' => ! $progress->isCompleted,
            'bg-emerald-500' => $progress->isCompleted,
        ])
        style="width: {{ $width }}%"
    ></div>
</div>
