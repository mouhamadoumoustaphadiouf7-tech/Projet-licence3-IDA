<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'SenAutoMarket') }} - Inscription</title>

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-[#0B0F1A] text-white font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-sm bg-white/5 border border-white/10 rounded-2xl overflow-hidden">

        <!-- BANDEAU VISUEL -->
        <div class="relative h-28 bg-gradient-to-br from-indigo-500 to-violet-600 flex items-end overflow-hidden">
            <div class="absolute -bottom-6 left-0 right-0 h-12 bg-white/5" style="border-radius: 50% 50% 0 0 / 100% 100% 0 0;"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="h-14 w-14 rounded-full bg-white/15 backdrop-blur flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="px-7 pt-6 pb-7">

            <!-- TITRE -->
            <p class="text-center text-xs font-semibold tracking-wider text-gray-200 uppercase">
                SEN<span class="text-indigo-400">AUTO</span>MARKET
            </p>
            <p class="text-center text-xs text-gray-500 mt-1.5 mb-6">
                Créez votre compte en quelques secondes
            </p>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- NAME -->
                <div>
                    <label for="name" class="block text-xs font-medium text-gray-400 mb-1">Nom</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}"
                           required autofocus autocomplete="name"
                           placeholder="Ex : Awa Diop"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                    @error('name')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- EMAIL -->
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-400 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autocomplete="username"
                           placeholder="vous@exemple.com"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                    @error('email')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PASSWORD -->
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-400 mb-1">Mot de passe</label>
                    <input id="password" type="password" name="password"
                           required autocomplete="new-password"
                           placeholder="••••••••"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                    @error('password')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CONFIRM PASSWORD -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-gray-400 mb-1">Confirmer le mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           required autocomplete="new-password"
                           placeholder="••••••••"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                    @error('password_confirmation')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ROLE -->
                <div>
                    <label for="role" class="block text-xs font-medium text-gray-400 mb-1">Type de compte</label>
                    <select id="role" name="role"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                        <option value="client" class="bg-[#0B0F1A]" @selected(old('role') === 'client')>Client</option>
                        <option value="vendeur" class="bg-[#0B0F1A]" @selected(old('role') === 'vendeur')>Vendeur / Concessionnaire</option>
                    </select>
                    @error('role')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SUBMIT -->
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition !mt-6">
                    Créer mon compte
                </button>

            </form>

            <!-- LIEN CONNEXION -->
            <p class="text-center text-xs text-gray-500 mt-5">
                Déjà inscrit ?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium transition">
                    Se connecter
                </a>
            </p>

        </div>

    </div>

</div>

</body>
</html>
