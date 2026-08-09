@props([
    'action',
    'title',
    'description',
    'status',
    'submitLabel',
    'method' => 'PATCH',
])

<form method="POST" action="{{ $action }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
    @csrf
    @if (! in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif

    <div class="mb-5 flex items-start justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ $description }}</p>
        </div>
        @if (session('status') === $status)
            <span class="shrink-0 text-xs font-medium text-emerald-600">Сохранено</span>
        @endif
    </div>

    {{ $slot }}

    <div class="mt-5 flex justify-end">
        <button type="submit" class="rounded-full bg-slate-900 px-4 py-2.5 text-xs font-medium text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
            {{ $submitLabel }}
        </button>
    </div>
</form>
