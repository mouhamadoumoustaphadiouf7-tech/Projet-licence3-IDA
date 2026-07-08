@extends('layouts.app')

@section('content')

    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- BREADCRUMB -->
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
            <a href="/" class="hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9.5L12 3l9 6.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10v9a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-4h4v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <span>/</span>
            <span class="text-gray-200">Voitures</span>
        </div>

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-white">Liste des voitures</h2>

            @if(in_array(auth()->user()->role, ['vendeur', 'admin']))
                <a href="/voitures/create"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white px-4 py-2.5 rounded-lg font-medium hover:opacity-90 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                    </svg>
                    Ajouter une voiture
                </a>
            @endif
        </div>

        <!-- FILTRE -->
        <form method="GET" action="/voitures" class="bg-white/5 p-4 rounded-xl border border-white/10 mb-8 flex flex-wrap gap-4 items-center">

            <div class="relative w-full md:w-1/4">
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M21 21l-4.3-4.3" stroke-linecap="round"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher (marque ou modèle)"
                       class="bg-white/5 border border-white/10 rounded-lg pl-9 pr-3 py-2.5 w-full text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
            </div>

            <select name="type" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 w-full md:w-1/4 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                <option value="" class="bg-[#0B0F1A]">Tous les types</option>
                <option value="vente" class="bg-[#0B0F1A]" @selected(request('type') === 'vente')>Vente</option>
                <option value="location" class="bg-[#0B0F1A]" @selected(request('type') === 'location')>Location</option>
            </select>

            <input type="number" name="prix_max" value="{{ request('prix_max') }}"
                   placeholder="Prix max"
                   class="bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 w-full md:w-1/5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">

            <button class="inline-flex items-center gap-2 bg-white/10 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-white/20 transition ml-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16l-6 8v6l-4 2v-8L4 4z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Filtrer
            </button>
        </form>

        <!-- GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">

            @forelse($voitures as $voiture)
                <div class="bg-white/5 rounded-xl border border-white/10 hover:border-indigo-400/30 hover:bg-white/[0.07] transition overflow-hidden flex flex-col">

                    <!-- IMAGE -->
                    <div class="relative h-44 w-full overflow-hidden bg-white/5">
                        @if($voiture->image)
                            <img src="{{ asset('storage/' . $voiture->image) }}"
                                 alt="{{ $voiture->marque }} {{ $voiture->modele }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-500 text-sm">
                                Pas d'image
                            </div>
                        @endif

                        @if($voiture->statut === 'disponible')
                            <span class="absolute top-3 left-3 text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-md bg-gradient-to-r from-indigo-500 to-violet-600 text-white">
                                Vedette
                            </span>
                        @endif

                        <span class="absolute top-3 right-11 text-xs font-medium px-2 py-1 rounded-md bg-black/60 text-white">
                            {{ $voiture->annee }}
                        </span>

                        <!-- FAVORI -->
                        <button type="button"
                                class="absolute top-3 right-3 h-8 w-8 rounded-full bg-black/50 backdrop-blur shadow flex items-center justify-center hover:bg-black/70 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 21s-7.5-4.6-10-9.1C.5 8.5 2 5 5.5 5c2 0 3.3 1.1 4.5 2.6C11.2 6.1 12.5 5 14.5 5 18 5 19.5 8.5 18 11.9 15.5 16.4 12 21 12 21z" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>

                    <!-- CONTENU -->
                    <div class="p-4 flex flex-col flex-1">

                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $voiture->marque }}</p>

                        <div class="flex justify-between items-start gap-2 mt-0.5">
                            <h3 class="font-bold text-base text-white">
                                {{ $voiture->modele }}
                            </h3>

                            @if($voiture->statut === 'disponible')
                                <span class="shrink-0 text-xs font-medium px-2 py-1 rounded-full bg-green-500/10 text-green-400">
                                    Disponible
                                </span>
                            @else
                                <span class="shrink-0 text-xs font-medium px-2 py-1 rounded-full bg-white/10 text-gray-400">
                                    {{ ucfirst($voiture->statut) }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 mt-2">
                            @if($voiture->type === 'vente')
                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-indigo-500/10 text-indigo-300">
                                    Vente
                                </span>
                            @else
                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-blue-500/10 text-blue-300">
                                    Location
                                </span>
                            @endif
                        </div>

                        <p class="text-white font-bold text-lg mt-2">
                            {{ number_format($voiture->prix, 0, ',', ' ') }} FCFA
                            @if($voiture->type === 'location')
                                <span class="text-sm font-medium text-gray-500">/ jour</span>
                            @endif
                        </p>

                        <p class="text-sm text-gray-400 mt-1 line-clamp-2">
                            {{ $voiture->description }}
                        </p>

                        <!-- ACTIONS -->
                        <div class="mt-4 pt-4 border-t border-white/10 flex items-center gap-2">

                            <a href="/voitures/{{ $voiture->id }}"
                               class="flex-1 inline-flex items-center justify-center gap-2 border border-white/10 text-gray-300 px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Voir détails
                            </a>

                            @if(auth()->user()->role == 'client')
                                <form method="POST" action="/voitures/{{ $voiture->id }}/reserver" class="flex-1">
                                    @csrf
                                    <button class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                                            <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                                        </svg>
                                        Réserver
                                    </button>
                                </form>
                            @endif

                            @if(in_array(auth()->user()->role, ['vendeur', 'admin']))
                                <a href="/voitures/{{ $voiture->id }}/edit"
                                   class="flex-1 inline-flex items-center justify-center gap-2 bg-white/10 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/20 transition">
                                    Modifier
                                </a>

                                <form action="/voitures/{{ $voiture->id }}" method="POST"
                                      onsubmit="return confirm('Supprimer cette voiture ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-red-500/20 text-red-400 hover:bg-red-500/10 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16z" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif

                        </div>

                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 py-16">
                    Aucune voiture trouvée.
                </div>
            @endforelse

        </div>

        <!-- PAGINATION -->
        @if($voitures instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-8">
                <p class="text-sm text-gray-400">
                    Affichage de {{ $voitures->firstItem() }} à {{ $voitures->lastItem() }} sur {{ $voitures->total() }} voitures
                </p>

                <div>
                    {{ $voitures->links() }}
                </div>
            </div>
        @endif

    </div>

@endsection
