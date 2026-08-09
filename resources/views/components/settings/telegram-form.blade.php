@props(['chat'])

<section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-semibold text-slate-900">Telegram</h2>
                <span class="size-1.5 rounded-full {{ $chat ? 'bg-emerald-300' : 'bg-slate-300' }}"></span>
            </div>
            <p class="mt-1 text-xs text-slate-400">
                {{ $chat ? 'Бот подключён к вашему аккаунту' : 'Добавляйте транзакции сообщением боту' }}
            </p>
        </div>

        @if ($chat)
            <form method="POST" action="{{ route('settings.telegram.destroy') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-slate-400 transition hover:text-rose-500">
                    Отключить
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('settings.telegram.token') }}">
                @csrf
                <button type="submit" class="rounded-full bg-slate-900 px-4 py-2.5 text-xs font-medium text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Получить код
                </button>
            </form>
        @endif
    </div>

    @if (session('telegram_link_token'))
        <div class="mt-5 rounded-xl px-4 py-3 ring-1 ring-slate-200">
            <p class="text-xs text-slate-500">Отправьте боту эту команду в течение 15 минут:</p>
            <code class="mt-2 block select-all text-sm font-semibold tracking-wide text-slate-900">/start {{ session('telegram_link_token') }}</code>
        </div>
    @endif

    @if (session('status') === 'telegram-disconnected')
        <p class="mt-4 text-xs text-emerald-600">Telegram отключён</p>
    @endif
</section>
