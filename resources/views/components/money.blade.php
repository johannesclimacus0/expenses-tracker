@props([
    'amount',
    'sign' => '',
])

@php
    $settings = auth()->user()?->settings;
    $decimals = ($settings?->show_cents ?? true) ? 2 : 0;
    $symbol = $settings?->currency?->symbol() ?? '₽';
@endphp

{{ $sign }}{{ number_format((float) $amount, $decimals, ',', ' ') }} {{ $symbol }}
