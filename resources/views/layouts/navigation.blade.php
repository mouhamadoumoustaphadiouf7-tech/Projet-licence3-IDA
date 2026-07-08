<nav class="bg-[#0B0F1A] border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        <!-- LOGO -->
        <a href="/" class="text-xl font-bold text-white">
            <span class="text-indigo-500">SEN</span>AUTOMARKET
        </a>

        <!-- MENU -->
        <div class="hidden md:flex space-x-8 text-gray-300">
            <a href="/" class="hover:text-white">Accueil</a>
            <a href="/voitures" class="hover:text-white">Véhicules</a>
            <a href="#" class="hover:text-white">À propos</a>
            <a href="#" class="hover:text-white">Contact</a>
        </div>

        <!-- ACTIONS -->
        <div class="flex items-center space-x-4">

            @auth
                <!-- Si connecté -->
                <span class="text-gray-300 hidden md:block">
                    {{ auth()->user()->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-red-400 hover:text-red-500">
                        Déconnexion
                    </button>
                </form>
            @else
                <!-- Si NON connecté -->
                <a href="{{ route('login') }}" class="text-gray-300 hover:text-white">
                    Connexion
                </a>
            @endauth

            <!-- BOUTON VENDRE -->
            @auth
                @if(auth()->user()->role === 'vendeur')
                    <a href="/voitures/create"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                        Vendre un véhicule
                    </a>
                @endif
            @endauth

        </div>
    </div>
</nav>
