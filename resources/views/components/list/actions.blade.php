@props([
    'editRoute',
    'deleteRoute',
    'confirmation' => 'Удалить запись?',
])

<div class="hidden shrink-0 items-center gap-3 sm:flex">
    <a href="{{ $editRoute }}" class="text-xs text-slate-400 transition hover:text-slate-900">Изменить</a>
    <form method="POST" action="{{ $deleteRoute }}" onsubmit="return confirm(@js($confirmation))">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-slate-300 transition hover:text-red-600">Удалить</button>
    </form>
</div>

<details class="relative shrink-0 sm:hidden">
    <summary class="cursor-pointer list-none rounded-lg px-2 py-1 text-xs text-slate-400 transition hover:bg-stone-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">Ещё</summary>
    <div class="absolute right-0 z-10 mt-2 w-28 overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-200">
        <a href="{{ $editRoute }}" class="block px-3 py-2 text-xs text-slate-600 transition hover:bg-stone-50 hover:text-slate-900">Изменить</a>
        <form method="POST" action="{{ $deleteRoute }}" onsubmit="return confirm(@js($confirmation))">
            @csrf
            @method('DELETE')
            <button type="submit" class="block w-full px-3 py-2 text-left text-xs text-red-600 transition hover:bg-red-50">Удалить</button>
        </form>
    </div>
</details>
