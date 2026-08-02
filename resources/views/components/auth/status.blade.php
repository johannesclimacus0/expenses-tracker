@props([
    'message',
])

@if ($message)
    <div role="status" class="mb-5 rounded-xl border border-green-200 bg-green-100 px-3.5 py-2.5 text-xs text-green-800">
        {{$message}}
    </div>
@endif
