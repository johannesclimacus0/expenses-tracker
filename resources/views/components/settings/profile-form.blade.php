@props(['user'])

<form method="POST" action="{{ route('settings.profile.update') }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
    @csrf
    @method('PATCH')

    <div class="mb-5 flex items-start justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Профиль</h2>
            <p class="mt-1 text-xs text-slate-400">Имя и адрес для входа</p>
        </div>
        @if (session('status') === 'profile-updated')
            <span class="shrink-0 text-xs font-medium text-emerald-600">Сохранено</span>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block">
            <span class="mb-1.5 block text-xs font-medium text-slate-600">Имя<span class="text-red-500"> *</span></span>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-0">
            @error('name')
                <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="mb-1.5 block text-xs font-medium text-slate-600">Почта<span class="text-red-500"> *</span></span>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-500 focus:ring-0">
            @error('email')
                <span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="mt-5 flex justify-end">
        <button type="submit" class="rounded-full bg-slate-900 px-4 py-2.5 text-xs font-medium text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
            Сохранить профиль
        </button>
    </div>
</form>
