<header class="border-b border-white/10 bg-[#0B0F1A]/95 backdrop-blur sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <!-- LOGO -->
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

            <!-- LIENS CENTRAUX -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
                <a href="/" class="text-white hover:text-indigo-300 transition">Accueil</a>
                <a href="/voitures" class="text-gray-400 hover:text-white transition">Véhicules</a>
                <a href="/about" class="text-gray-400 hover:text-white transition">À propos</a>
                <a href="/contact" class="text-gray-400 hover:text-white transition">Contact</a>
            </nav>

            <!-- ACTIONS DROITE -->
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
