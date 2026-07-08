@extends('layouts.app')

@section('content')

    <div class="max-w-3xl mx-auto px-6 py-8">

        <!-- BREADCRUMB -->
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
            <a href="/" class="hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9.5L12 3l9 6.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10v9a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-4h4v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <span>/</span>
            <a href="/voitures" class="hover:text-white transition">Voitures</a>
            <span>/</span>
            <span class="text-gray-200">Modifier</span>
        </div>

        <!-- HEADER -->
        <h2 class="text-3xl font-bold text-white mb-6">Modifier voiture</h2>

        <!-- ERREURS DE VALIDATION -->
        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-300 rounded-lg p-4 mb-6 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORMULAIRE -->
        <form method="POST" action="/voitures/{{ $voiture->id }}" enctype="multipart/form-data"
              class="bg-white/5 p-6 rounded-xl border border-white/10 space-y-5">
            @csrf
            @method('PUT')

            <!-- IMAGE ACTUELLE -->
            @if($voiture->image)
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Image actuelle</label>
                    <img src="{{ asset('storage/' . $voiture->image) }}"
                         alt="{{ $voiture->marque }} {{ $voiture->modele }}"
                         class="h-32 w-48 object-cover rounded-lg border border-white/10">
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Marque</label>
                    <input type="text" name="marque" value="{{ old('marque', $voiture->marque) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Modèle</label>
                    <input type="text" name="modele" value="{{ old('modele', $voiture->modele) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Prix (FCFA)</label>
                    <input type="number" name="prix" value="{{ old('prix', $voiture->prix) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Type</label>
                    <select name="type"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                        <option value="vente" class="bg-[#0B0F1A]" {{ old('type', $voiture->type) == 'vente' ? 'selected' : '' }}>Vente</option>
                        <option value="location" class="bg-[#0B0F1A]" {{ old('type', $voiture->type) == 'location' ? 'selected' : '' }}>Location</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                    Nouvelle image <span class="text-gray-500 font-normal">(optionnel)</span>
                </label>
                <input type="file" name="image"
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-white/10 file:text-white file:text-sm hover:file:bg-white/20 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Description</label>
                <textarea name="description" rows="4"
                          class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">{{ old('description', $voiture->description) }}</textarea>
            </div>

            <!-- ACTIONS -->
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white px-5 py-2.5 rounded-lg font-medium hover:opacity-90 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Modifier
                </button>

                <a href="/voitures"
                   class="inline-flex items-center gap-2 border border-white/10 text-gray-300 px-5 py-2.5 rounded-lg font-medium hover:bg-white/5 hover:text-white transition">
                    Annuler
                </a>
            </div>

        </form>

    </div>

@endsection
