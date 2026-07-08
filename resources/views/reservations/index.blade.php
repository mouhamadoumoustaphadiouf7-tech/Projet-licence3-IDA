@extends('layouts.app')

@section('content')

    <div class="max-w-5xl mx-auto px-6 py-8">

        <!-- BREADCRUMB -->
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
            <a href="/" class="hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9.5L12 3l9 6.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10v9a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-4h4v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <span>/</span>
            <span class="text-gray-200">Mes réservations</span>
        </div>

        <h2 class="text-3xl font-bold text-white mb-6">Mes réservations</h2>

        <!-- ÉTAT VIDE -->
        @if($reservations->isEmpty())
            <div class="bg-white/5 rounded-xl border border-white/10 p-12 text-center">
                <div class="h-12 w-12 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                    </svg>
                </div>
                <p class="text-gray-400">Aucune réservation pour le moment</p>
            </div>
        @endif

        <!-- LISTE DES RÉSERVATIONS -->
        <div class="space-y-4">
            @foreach($reservations as $reservation)
                <div class="bg-white/5 rounded-xl border border-white/10 p-6">

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">

                        <div>
                            <h3 class="text-lg font-bold text-white">
                                {{ $reservation->voiture->marque }} - {{ $reservation->voiture->modele }}
                            </h3>

                            <p class="text-sm text-gray-400 mt-1">
                                <span class="font-medium text-gray-300">Client :</span>
                                {{ $reservation->user->name ?? 'N/A' }}
                            </p>

                            <p class="text-white font-bold text-lg mt-2">
                                {{ number_format($reservation->voiture->prix, 0, ',', ' ') }} FCFA
                            </p>

                            <p class="text-sm text-gray-400 mt-1 inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                                </svg>
                                {{ $reservation->date_reservation }}
                            </p>
                        </div>

                        <!-- STATUT -->
                        <div>
                            @if($reservation->statut == 'en_attente')
                                <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-orange-500/10 text-orange-400">
                                    En attente
                                </span>
                            @elseif($reservation->statut == 'validee' || $reservation->statut == 'validée')
                                <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-green-500/10 text-green-400">
                                    Validée
                                </span>
                            @elseif($reservation->statut == 'refusee' || $reservation->statut == 'refusée')
                                <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-red-500/10 text-red-400">
                                    Refusée
                                </span>
                            @else
                                <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-white/10 text-gray-400">
                                    {{ ucfirst($reservation->statut) }}
                                </span>
                            @endif
                        </div>

                    </div>

                    <!-- ACTIONS VENDEUR -->
                    @if(auth()->user()->role == 'vendeur' && $reservation->statut == 'en_attente')
                        <div class="flex items-center gap-3 mt-5 pt-5 border-t border-white/10">

                            <form method="POST" action="/reservations/{{ $reservation->id }}/valider">
                                @csrf
                                <button class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Valider
                                </button>
                            </form>

                            <form method="POST" action="/reservations/{{ $reservation->id }}/refuser">
                                @csrf
                                <button class="inline-flex items-center gap-2 border border-red-500/20 text-red-400 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-500/10 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Refuser
                                </button>
                            </form>

                        </div>
                    @endif

                </div>
            @endforeach
        </div>

    </div>

@endsection
