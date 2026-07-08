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
            <span class="text-gray-200">Utilisateurs</span>
        </div>

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-white">Liste des utilisateurs</h2>

            <a href="/users/create"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-violet-600 text-white px-4 py-2.5 rounded-lg font-medium hover:opacity-90 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Ajouter un utilisateur
            </a>
        </div>

        <!-- LISTE -->
        <div class="bg-white/5 rounded-xl border border-white/10 overflow-hidden">

            @forelse($users as $user)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 {{ !$loop->last ? 'border-b border-white/10' : '' }}">

                    <div class="flex items-center gap-3">
                        <span class="h-10 w-10 rounded-full bg-white/10 flex items-center justify-center text-sm font-semibold text-white shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <div>
                            <p class="font-medium text-white">{{ $user->name }}</p>
                            <p class="text-sm text-gray-400">{{ $user->email }}</p>
                        </div>

                        @if($user->role === 'admin')
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-violet-500/10 text-violet-300">
                                Admin
                            </span>
                        @elseif($user->role === 'vendeur')
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-300">
                                Vendeur
                            </span>
                        @else
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-300">
                                Client
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="/users/{{ $user->id }}/edit"
                           class="inline-flex items-center gap-2 border border-white/10 text-gray-300 px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 hover:text-white transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Modifier
                        </a>

                        <form action="/users/{{ $user->id }}" method="POST"
                              onsubmit="return confirm('Supprimer cet utilisateur ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-red-500/20 text-red-400 hover:bg-red-500/10 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="text-center text-gray-500 py-16">
                    Aucun utilisateur trouvé.
                </div>
            @endforelse

        </div>

    </div>

@endsection
