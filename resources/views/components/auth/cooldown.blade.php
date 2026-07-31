@props([
    'retryAfter',
    'message' => 'Повторная отправка будет доступна через',
])

<div role="status" data-throttle-message
    {{$attributes->class([
        'mb-4 rounded-xl border border-amber-200 bg-amber-50',
        'px-3.5 py-2.5 text-xs leading-5 text-amber-700',
    ])}}
>
    {{$message}}
    <span data-throttle-countdown>{{$retryAfter}}</span> сек
</div>
