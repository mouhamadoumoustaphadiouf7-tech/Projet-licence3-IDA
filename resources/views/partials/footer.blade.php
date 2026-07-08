<footer class="border-t border-white/10 mt-12">
    <div class="max-w-7xl mx-auto px-6 py-12">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-10">

            <!-- LOGO + DESCRIPTION -->
            <div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="font-bold text-white">
                        SEN<span class="text-indigo-400">AUTO</span>MARKET
                    </span>
                </div>

                <p class="text-sm text-gray-400 mt-4 max-w-xs">
                    La plateforme de référence pour acheter et vendre votre véhicule au Sénégal. Annonces vérifiées, contact direct avec les vendeurs.
                </p>
            </div>

            <!-- NAVIGATION -->
            <div>
                <h4 class="text-xs font-semibold text-gray-300 uppercase tracking-wider mb-4">Navigation</h4>
                <ul class="space-y-2.5 text-sm text-gray-400">
                    <li><a href="/voitures" class="hover:text-white transition">Tous les véhicules</a></li>
                    <li><a href="/about" class="hover:text-white transition">À propos</a></li>
                    <li><a href="/contact" class="hover:text-white transition">Contact</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Connexion</a></li>
                </ul>
            </div>

            <!-- CONTACT -->
            <div>
                <h4 class="text-xs font-semibold text-gray-300 uppercase tracking-wider mb-4">Contact</h4>
                <ul class="space-y-2.5 text-sm text-gray-400">
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        +221 77 000 00 00
                    </li>
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        contact@senmarketauto.sn
                    </li>
                </ul>

                <div class="flex items-center gap-3 mt-4">
                    <a href="#" class="h-8 w-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22 12a10 10 0 1 0-11.5 9.9v-7H8v-2.9h2.5V9.8c0-2.5 1.5-3.9 3.7-3.9 1.1 0 2.2.2 2.5.3v2.7h-1.4c-1.4 0-1.8.8-1.8 1.7v2.1H17l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z"/>
                        </svg>
                    </a>
                    <a href="#" class="h-8 w-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        <!-- BAS DE PAGE -->
        <div class="border-t border-white/10 mt-10 pt-6">
            <p class="text-center text-sm text-gray-500">
                © {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés — Dakar, Sénégal.
            </p>
        </div>

    </div>
</footer>
