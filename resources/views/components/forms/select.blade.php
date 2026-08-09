@props([
    'name',
    'label',
    'error' => null,
    'variant' => 'default',
])

<div>
    <label for="{{ $name }}" @class([
        'block text-xs',
        'mb-1.5 font-medium text-slate-600' => $variant === 'default',
        'mb-1 text-slate-400' => $variant === 'filter',
    ])>
        {{ $label }}@if ($attributes->has('required'))<span class="text-red-500"> *</span>@endif
    </label>

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->class([
            'block w-full outline-none transition',
            'h-10 rounded-xl border border-transparent bg-stone-50 px-3.5 text-xs text-slate-900 hover:border-slate-300 focus:border-slate-500 focus:bg-white' => $variant === 'default',
            'h-8 border-0 border-b border-slate-200 bg-transparent px-0 text-xs text-slate-700 hover:border-slate-400 focus:border-slate-500 focus:bg-white focus:ring-0' => $variant === 'filter',
        ]) }}
    >
        {{ $slot }}
    </select>

    @error($error ?? $name)
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
