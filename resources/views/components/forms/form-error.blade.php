@props([
    'key' => 'auth',
])

@if ($errors->has($key))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
        {{ $errors->first($key) }}
    </div>
@endif
