@props([
    'message',
])

@if ($message)
    <div role="status" class="mb-5 rounded-xl border border-lime-200 bg-lime-50 px-3.5 py-2.5 text-xs text-lime-800">
        {{$message}}
    </div>
@endif
