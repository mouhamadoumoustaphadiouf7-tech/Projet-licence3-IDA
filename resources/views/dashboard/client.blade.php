@extends('layouts.app')

@section('content')

    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-white">Dashboard Client</h2>

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

        <!-- STATISTIQUE -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">

            <div class="bg-white/5 rounded-xl border border-white/10 p-6 flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Mes réservations</p>
                    <p class="text-2xl font-bold text-white">{{ auth()->user()->reservations->count() }}</p>
                </div>
            </div>

        </div>

        <!-- ACTIONS -->
        <h3 class="text-lg font-semibold text-white mb-4">Actions rapides</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <a href="/voitures" class="group bg-white/5 rounded-xl border border-white/10 p-6 hover:bg-white/[0.08] hover:border-indigo-400/30 transition flex items-start gap-4">
                <div class="h-11 w-11 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-white group-hover:text-indigo-300 transition">Voir voitures</h4>
                    <p class="text-sm text-gray-400 mt-1">Parcourir les véhicules disponibles</p>
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
                    <h4 class="font-semibold text-white group-hover:text-indigo-300 transition">Mes réservations</h4>
                    <p class="text-sm text-gray-400 mt-1">Suivre vos réservations en cours</p>
                </div>
            </a>

        </div>

    </div>

@endsection
