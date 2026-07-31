<nav class="border-b border-slate-200 bg-stone-100/95">
    <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
        <div class="flex items-center">
            <div class="flex items-center gap-1 rounded-full bg-white p-1 shadow-sm">
                <x-navigation.link
                    :href="route('dashboard')"
                    :active="request()->routeIs('dashboard')"
                >
                    Dashboard
                </x-navigation.link>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden text-xs text-slate-500 sm:inline">
                {{ auth()->user()->name }}
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-full bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition hover:bg-slate-900 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100">
                    Выйти
                </button>
            </form>
        </div>
    </div>
</nav>
