@props([
    'title' => config('app.name'),
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-slate-900 antialiased">
<x-navigation.navbar />
<main class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
    {{ $slot }}
</main>
</body>
</html>
