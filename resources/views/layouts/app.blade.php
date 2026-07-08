<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SenAutoMarket') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#0B0F1A] text-white font-sans antialiased">

<!-- NAVBAR -->
@include('layouts.navigation')

<!-- HEADER (optionnel) -->
@isset($header)
    <header class="px-6 py-4 border-b border-white/10">
        {{ $header }}
    </header>
@endisset

<!-- FLASH MESSAGE -->
@if(session('success'))
    <div class="max-w-7xl mx-auto mt-4 p-3 bg-green-600/20 border border-green-500/30 text-green-300 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="max-w-7xl mx-auto mt-4 p-3 bg-red-600/20 border border-red-500/30 text-red-300 rounded-lg text-sm">
        {{ session('error') }}
    </div>
@endif

<!-- CONTENT -->
<main class="max-w-7xl mx-auto px-6 py-8">
    @yield('content')
</main>

@include('partials.footer')
</body>
</html>
