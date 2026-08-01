@props([
    'categories',
])

<form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-2 gap-x-4 gap-y-4 border-y border-slate-200 py-4 sm:grid-cols-4 lg:sticky lg:top-6 lg:grid-cols-1 lg:border-y-0 lg:border-r lg:py-1 lg:pr-5">
    <div>
        <label for="type" class="mb-1 block text-xs text-slate-400">Тип</label>
        <select id="type" name="type" class="block h-8 w-full border-0 border-b border-slate-200 bg-transparent px-0 text-xs text-slate-700 outline-none transition hover:border-slate-400 focus:border-slate-500 focus:bg-white focus:ring-0">
            <option value="">Все</option>
            <option value="expense" @selected(request('type') === 'expense')>Расходы</option>
            <option value="income" @selected(request('type') === 'income')>Доходы</option>
        </select>
    </div>

    <div>
        <label for="category" class="mb-1 block text-xs text-slate-400">Категория</label>
        <select id="category" name="category" class="block h-8 w-full border-0 border-b border-slate-200 bg-transparent px-0 text-xs text-slate-700 outline-none transition hover:border-slate-400 focus:border-slate-500 focus:bg-white focus:ring-0">
            <option value="">Все</option>
            @foreach ($categories as $category)
                <option value="{{ $category->uuid }}" @selected(request('category') === $category->uuid)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="from" class="mb-1 block text-xs text-slate-400">С даты</label>
        <input id="from" name="from" type="date" value="{{ request('from') }}" class="block h-8 w-full border-0 border-b border-slate-200 bg-transparent px-0 text-xs text-slate-700 outline-none transition hover:border-slate-400 focus:border-slate-500 focus:bg-white focus:ring-0">
    </div>
    <div>
        <label for="to" class="mb-1 block text-xs text-slate-400">По дату</label>
        <input id="to" name="to" type="date" value="{{ request('to') }}" class="block h-8 w-full border-0 border-b border-slate-200 bg-transparent px-0 text-xs text-slate-700 outline-none transition hover:border-slate-400 focus:border-slate-500 focus:bg-white focus:ring-0">
    </div>

    <div class="col-span-2 flex items-center gap-3 sm:col-span-4 lg:col-span-1 lg:flex-col lg:items-start">
        <button type="submit" class="text-xs font-medium text-slate-900 underline decoration-slate-300 underline-offset-4 transition hover:decoration-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
            Показать
        </button>
        <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500">
            Сбросить
        </a>
    </div>
</form>
