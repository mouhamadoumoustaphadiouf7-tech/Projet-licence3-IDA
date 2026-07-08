@extends('layouts.app')

@section('content')

    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-white">Dashboard Vendeur</h2>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 border border-white/10 text-gray-300 px-4 py-2.5 rounded-lg font-medium hover:bg-white/5 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Se déconnecter
                </button>
            </form>
        </div>

        <!-- STATISTIQUES -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">

            <div class="bg-white/5 rounded-xl border border-white/10 p-6 flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="7.5" cy="16" r="0.5" fill="currentColor"/>
                        <circle cx="16.5" cy="16" r="0.5" fill="currentColor"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Mes voitures</p>
                    <p class="text-2xl font-bold text-white">{{ auth()->user()->voitures->count() }}</p>
                </div>
            </div>

            <div class="bg-white/5 rounded-xl border border-white/10 p-6 flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Mes réservations</p>
                    <p class="text-2xl font-bold text-white">
                        {{ \App\Models\Reservation::whereHas('voiture', function($q){
                            $q->where('user_id', auth()->id());
                        })->count() }}
                    </p>
                </div>
            </div>

        </div>

        <!-- ACTIONS -->
        <h3 class="text-lg font-semibold text-white mb-4">Actions rapides</h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            <a href="/voitures" class="group bg-white/5 rounded-xl border border-white/10 p-6 hover:bg-white/[0.08] hover:border-indigo-400/30 transition flex items-start gap-4">
                <div class="h-11 w-11 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-white group-hover:text-indigo-300 transition">Mes voitures</h4>
                    <p class="text-sm text-gray-400 mt-1">Gérer vos annonces publiées</p>
                </div>
            </a>

            <a href="/voitures/create" class="group bg-white/5 rounded-xl border border-white/10 p-6 hover:bg-white/[0.08] hover:border-indigo-400/30 transition flex items-start gap-4">
                <div class="h-11 w-11 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-white group-hover:text-indigo-300 transition">Ajouter voiture</h4>
                    <p class="text-sm text-gray-400 mt-1">Publier une nouvelle annonce</p>
                </div>
            </a>

            <a href="/mes-reservations" class="group bg-white/5 rounded-xl border border-white/10 p-6 hover:bg-white/[0.08] hover:border-indigo-400/30 transition flex items-start gap-4">
                <div class="h-11 w-11 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-white group-hover:text-indigo-300 transition">Voir réservations</h4>
                    <p class="text-sm text-gray-400 mt-1">Consulter les demandes reçues</p>
                </div>
            </a>

        </div>

    </div>

@endsection
