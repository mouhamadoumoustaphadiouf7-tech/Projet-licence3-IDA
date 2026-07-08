@extends('layouts.app')

@section('content')

    <div class="max-w-2xl mx-auto px-6 py-8">

        <!-- BREADCRUMB -->
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
            <a href="/" class="hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9.5L12 3l9 6.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10v9a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-4h4v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <span>/</span>
            <a href="/users" class="hover:text-white transition">Utilisateurs</a>
            <span>/</span>
            <span class="text-gray-200">Modifier</span>
        </div>

        <!-- HEADER -->
        <h2 class="text-3xl font-bold text-white mb-6">Modifier utilisateur</h2>

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
        <form method="POST" action="/users/{{ $user->id }}"
              class="bg-white/5 p-6 rounded-xl border border-white/10 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Nom</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Rôle</label>
                <select name="role"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400/50">
                    <option value="client" class="bg-[#0B0F1A]" {{ $user->role == 'client' ? 'selected' : '' }}>Client</option>
                    <option value="vendeur" class="bg-[#0B0F1A]" {{ $user->role == 'vendeur' ? 'selected' : '' }}>Vendeur</option>
                    <option value="admin" class="bg-[#0B0F1A]" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
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

                <a href="/users"
                   class="inline-flex items-center gap-2 border border-white/10 text-gray-300 px-5 py-2.5 rounded-lg font-medium hover:bg-white/5 hover:text-white transition">
                    Annuler
                </a>
            </div>

        </form>

    </div>

@endsection
