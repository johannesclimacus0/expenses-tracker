@props([
    'title',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-slate-900 antialiased">
<div class="min-h-screen">
    <x-navigation.guest-navbar />

    <main class="mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-5xl place-items-center px-4 py-8 sm:px-6">
        <section class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-5 shadow-lg sm:p-6">
            {{$slot}}
        </section>
    </main>
</div>
</body>
</html>
