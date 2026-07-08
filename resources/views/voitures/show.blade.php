@extends('layouts.app')

@section('content')

    <div class="max-w-6xl mx-auto px-6 py-8">

        <!-- BREADCRUMB -->
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
            <a href="/" class="hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9.5L12 3l9 6.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10v9a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-4h4v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <span>/</span>
            <a href="/voitures" class="hover:text-white transition">Voitures</a>
            <span>/</span>
            <span class="text-gray-200">{{ $voiture->marque }} {{ $voiture->modele }}</span>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">

            <!-- COLONNE IMAGE + DESCRIPTION -->
            <div class="lg:col-span-2">

                <div class="relative rounded-2xl overflow-hidden bg-white/5 border border-white/10 h-72 lg:h-96">
                    @if($voiture->image)
                        <img src="{{ asset('storage/' . $voiture->image) }}"
                             alt="{{ $voiture->marque }} {{ $voiture->modele }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="flex items-center justify-center h-full text-gray-500">
                            Pas d'image
                        </div>
                    @endif

                    @if(isset($voiture->annee))
                        <span class="absolute top-4 right-4 text-sm font-medium px-3 py-1.5 rounded-md bg-black/60 text-white">
                            {{ $voiture->annee }}
                        </span>
                    @endif
                </div>

                <!-- DESCRIPTION -->
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 mt-6">
                    <h3 class="font-semibold text-white text-lg mb-3">Description</h3>
                    <p class="text-gray-400 leading-relaxed">
                        {{ $voiture->description }}
                    </p>
                </div>

            </div>

            <!-- COLONNE INFOS + ACTIONS -->
            <div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 sticky top-24">

                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $voiture->marque }}</p>
                    <h1 class="text-2xl font-bold text-white mt-1">
                        {{ $voiture->modele }}
                    </h1>

                    <!-- BADGES -->
                    <div class="flex items-center gap-2 mt-3">
                        @if($voiture->type === 'vente')
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-300">
                                Vente
                            </span>
                        @else
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-300">
                                Location
                            </span>
                        @endif

                        @if($voiture->statut === 'disponible')
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-500/10 text-green-400">
                                Disponible
                            </span>
                        @else
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-white/10 text-gray-400">
                                {{ ucfirst($voiture->statut) }}
                            </span>
                        @endif
                    </div>

                    <!-- PRIX -->
                    <p class="text-white font-bold text-3xl mt-5">
                        {{ number_format($voiture->prix, 0, ',', ' ') }} FCFA
                        @if($voiture->type === 'location')
                            <span class="text-base font-medium text-gray-500">/ jour</span>
                        @endif
                    </p>

                    <!-- ACTIONS -->
                    <div class="mt-6 space-y-3">

                        @if(auth()->check() && auth()->user()->role == 'client')
                            <form method="POST" action="/voitures/{{ $voiture->id }}/reserver">
                                @csrf
                                <button class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white px-4 py-3 rounded-lg text-sm font-medium hover:opacity-90 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                                        <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                                    </svg>
                                    Réserver
                                </button>
                            </form>
                        @endif

                        @if(auth()->check() && in_array(auth()->user()->role, ['vendeur', 'admin']))
                            <a href="/voitures/{{ $voiture->id }}/edit"
                               class="w-full inline-flex items-center justify-center gap-2 bg-white/10 text-white px-4 py-3 rounded-lg text-sm font-medium hover:bg-white/20 transition">
                                Modifier l'annonce
                            </a>
                        @endif

                        <a href="/voitures"
                           class="w-full inline-flex items-center justify-center gap-2 border border-white/10 text-gray-300 px-4 py-3 rounded-lg text-sm font-medium hover:bg-white/5 hover:text-white transition">
                            Retour à la liste
                        </a>

                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection
