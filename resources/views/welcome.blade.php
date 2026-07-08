<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'SenAutoMarket') }}</title>

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-[#0B0F1A] text-white font-sans antialiased">

<!-- NAVBAR -->
<header class="border-b border-white/10 bg-[#0B0F1A]/95 backdrop-blur sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <a href="/" class="flex items-center gap-2 shrink-0">
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="font-bold text-white text-lg">
                    SEN<span class="text-indigo-400">AUTO</span>MARKET
                </span>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
                <a href="/" class="text-white hover:text-indigo-300 transition">Accueil</a>
                <a href="/voitures" class="text-gray-400 hover:text-white transition">Véhicules</a>
                <a href="#" class="text-gray-400 hover:text-white transition">À propos</a>
                <a href="#" class="text-gray-400 hover:text-white transition">Contact</a>
            </nav>

            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:opacity-90 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-sm font-medium text-gray-300 hover:text-white transition px-3 py-2">
                            Connexion
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:opacity-90 transition">
                                Vendre un véhicule
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif

        </div>
    </div>
</header>

<!-- HERO -->
<main class="max-w-7xl mx-auto px-6 py-16 lg:py-20">

    <span class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-gray-300 mb-6">
        <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
        Marketplace #1 au Sénégal
    </span>

    <div class="grid lg:grid-cols-2 gap-12 items-center">

        <div>
            <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight">
                Le marché auto
                 <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">nouvelle génération</span>.
            </h1>

            <p class="text-gray-400 text-lg mt-4 max-w-md">
                Des milliers d'annonces de voitures neuves et d'occasions au Sénégal. Rechercher, comparer et contacter directement les vendeurs.
            </p>

            <!-- BARRE DE RECHERCHE -->
            <form method="GET" action="/voitures" class="flex items-center gap-3 mt-8 bg-white/5 border border-white/10 rounded-xl p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 ml-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M21 21l-4.3-4.3" stroke-linecap="round"/>
                </svg>
                <input type="text" name="search" placeholder="Marque, modèles, villes"
                       class="flex-1 bg-transparent text-sm text-white placeholder-gray-500 focus:outline-none">
                <button hidden="hidden" type="submit"
                        class="inline-flex items-center bg-gradient-to-r from-indigo-500 to-violet-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:opacity-90 transition shrink-0">
                    Rechercher
                </button>
            </form>

            <div class="flex items-center gap-2 mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z" stroke-linejoin="round"/>
                    <path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div class="text-xs text-gray-400">
                    <span class="text-white font-medium">Vendeurs vérifiés</span> · Transactions sécurisées
                </div>
            </div>

            <!-- STATS -->
            <div class="grid grid-cols-3 gap-6 mt-10">
                <div>
                    <p class="text-2xl font-bold text-white">2 500+</p>
                    <p class="text-xs text-gray-500 mt-1">Véhicules</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">14</p>
                    <p class="text-xs text-gray-500 mt-1">Régions</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">98%</p>
                    <p class="text-xs text-gray-500 mt-1">Satisfait</p>
                </div>
            </div>
        </div>

        <!-- VISUEL -->
        <div class="relative h-80 lg:h-[420px] rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-950 to-[#0B0F1A] border border-white/10">
            <div class="absolute inset-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-indigo-500/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="7.5" cy="16" r="0.5" fill="currentColor"/>
                    <circle cx="16.5" cy="16" r="0.5" fill="currentColor"/>
                </svg>
            </div>
        </div>

    </div>
</main>

<!-- VÉHICULES VEDETTES -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="flex justify-between items-end mb-6">
        <div>
            <p class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-1">À la une</p>
            <h2 class="text-2xl font-bold text-white">Véhicules vedettes</h2>
        </div>
        <a href="/voitures" class="text-sm text-gray-400 hover:text-white transition inline-flex items-center gap-1">
            Tout voir
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($vedettes ?? [] as $voiture)
            <a href="/voitures/{{ $voiture->id }}" class="group bg-white/5 rounded-xl border border-white/10 hover:border-indigo-400/30 hover:bg-white/[0.07] transition overflow-hidden">
                <div class="relative h-36 w-full bg-white/5">
                    @if($voiture->image)
                        <img src="{{ asset('storage/' . $voiture->image) }}" class="w-full h-full object-cover">
                    @endif
                    <span class="absolute top-2 left-2 text-[10px] font-bold uppercase px-2 py-1 rounded-md bg-gradient-to-r from-indigo-500 to-violet-600 text-white">
                        Vedette
                    </span>
                    <span class="absolute top-2 right-2 text-xs font-medium px-2 py-1 rounded-md bg-black/60 text-white">
                        {{ $voiture->annee }}
                    </span>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 uppercase">{{ $voiture->marque }}</p>
                    <h3 class="font-semibold text-white">{{ $voiture->modele }}</h3>
                    <p class="text-white font-bold mt-2">{{ number_format($voiture->prix, 0, ',', ' ') }} FCFA</p>
                </div>
            </a>
        @empty
            <p class="col-span-full text-center text-gray-500 py-10">Aucun véhicule à la une pour le moment.</p>
        @endforelse
    </div>
</section>

<!-- COMMENT ÇA MARCHE -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <p class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-1">Comment ça marche</p>
    <h2 class="text-2xl font-bold text-white mb-8">Acheter ou vendre en 3 étapes</h2>

    <div class="grid sm:grid-cols-3 gap-6">

        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="h-9 w-9 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M21 21l-4.3-4.3" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="text-2xl font-bold text-white/10">01</span>
            </div>
            <h3 class="font-semibold text-white text-lg">Rechercher</h3>
            <p class="text-sm text-gray-400 mt-2">Filtrez par marque, prix, année, carburant et localisation pour trouver le véhicule parfait.</p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="h-9 w-9 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="8" r="3.5"/>
                        <path d="M2.5 19c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6" stroke-linecap="round"/>
                        <path d="M16 4.3a3.5 3.5 0 0 1 0 6.9M21.5 19c0-2.8-2.1-5.2-5-5.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="text-2xl font-bold text-white/10">02</span>
            </div>
            <h3 class="font-semibold text-white text-lg">Contactez</h3>
            <p class="text-sm text-gray-400 mt-2">Discutez directement avec le vendeur via téléphone ou messagerie intégrée.</p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="h-9 w-9 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-2xl font-bold text-white/10">03</span>
            </div>
            <h3 class="font-semibold text-white text-lg">Concluez</h3>
            <p class="text-sm text-gray-400 mt-2">Organisez un essai et finalisez votre achat en toute confiance.</p>
        </div>

    </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-6 pb-20">
    <div class="bg-gradient-to-br from-indigo-950 to-violet-950 border border-white/10 rounded-2xl p-10 text-center">
        <h2 class="text-2xl lg:text-3xl font-bold text-white">
            Prêt à vendre votre <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">véhicule</span> ?
        </h2>
        <p class="text-gray-400 mt-3 max-w-md mx-auto">
            Publiez votre annonce gratuitement et touchez des milliers d'acheteurs au Sénégal.
        </p>

        @guest
            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition mt-6">
                    Publier une annonce
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @endif
        @endguest
    </div>
</section>

@include('partials.footer')

</body>
</html>
