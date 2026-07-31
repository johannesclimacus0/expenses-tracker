<nav class="relative z-10 bg-transparent">
    <div class="mx-auto flex h-16 w-full max-w-5xl items-center justify-end px-4 sm:px-6">
        <div class="flex items-center gap-1 rounded-full bg-white p-1 shadow-sm">
            <x-navigation.link
                :href="route('login')"
                :active="request()->routeIs('login')"
            >Вход
            </x-navigation.link>

            <x-navigation.link
                :href="route('register')"
                :active="request()->routeIs('register')"
            >
                Регистрация
            </x-navigation.link>
        </div>
    </div>
</nav>
