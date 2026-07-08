<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @php
                $role = auth()->user()->role;
            @endphp

                <!-- BANNIÈRE DE BIENVENUE -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                @if($role == 'admin')
                    <h3 class="text-2xl font-bold text-gray-900">Bienvenue Admin 👨‍💼</h3>
                    <p class="text-sm text-gray-500 mt-1">Gérez les voitures, les réservations et les utilisateurs.</p>

                @elseif($role == 'vendeur')
                    <h3 class="text-2xl font-bold text-gray-900">Bienvenue Vendeur 🏪</h3>
                    <p class="text-sm text-gray-500 mt-1">Suivez et gérez vos annonces de voitures.</p>

                @else
                    <h3 class="text-2xl font-bold text-gray-900">Bienvenue Client 👤</h3>
                    <p class="text-sm text-gray-500 mt-1">Parcourez les voitures et suivez vos réservations.</p>
                @endif
            </div>

            <!-- CARTES D'ACTIONS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                @if($role == 'admin')

                    <a href="/voitures" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-red-200 transition flex items-start gap-4">
                        <div class="h-11 w-11 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="7.5" cy="16" r="0.5" fill="currentColor"/>
                                <circle cx="16.5" cy="16" r="0.5" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-red-600 transition">Gérer les voitures</h4>
                            <p class="text-sm text-gray-500 mt-1">Voir, modifier ou supprimer les annonces</p>
                        </div>
                    </a>

                    <a href="/mes-reservations" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-red-200 transition flex items-start gap-4">
                        <div class="h-11 w-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-red-600 transition">Voir les réservations</h4>
                            <p class="text-sm text-gray-500 mt-1">Consulter toutes les réservations</p>
                        </div>
                    </a>

                    <a href="/users" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-red-200 transition flex items-start gap-4">
                        <div class="h-11 w-11 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="8" r="3.5"/>
                                <path d="M2.5 19c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6" stroke-linecap="round"/>
                                <path d="M16 4.3a3.5 3.5 0 0 1 0 6.9M21.5 19c0-2.8-2.1-5.2-5-5.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-red-600 transition">Gérer les utilisateurs</h4>
                            <p class="text-sm text-gray-500 mt-1">Ajouter, modifier ou supprimer des comptes</p>
                        </div>
                    </a>

                @elseif($role == 'vendeur')

                    <a href="/voitures" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-red-200 transition flex items-start gap-4">
                        <div class="h-11 w-11 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="7.5" cy="16" r="0.5" fill="currentColor"/>
                                <circle cx="16.5" cy="16" r="0.5" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-red-600 transition">Mes voitures</h4>
                            <p class="text-sm text-gray-500 mt-1">Voir et gérer vos annonces</p>
                        </div>
                    </a>

                    <a href="/voitures/create" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-red-200 transition flex items-start gap-4">
                        <div class="h-11 w-11 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-red-600 transition">Ajouter une voiture</h4>
                            <p class="text-sm text-gray-500 mt-1">Publier une nouvelle annonce</p>
                        </div>
                    </a>

                @else

                    <a href="/voitures" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-red-200 transition flex items-start gap-4">
                        <div class="h-11 w-11 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 13l1.5-5A2 2 0 0 1 6.4 6.5h11.2a2 2 0 0 1 1.9 1.5L21 13M5 13h14v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5z" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="7.5" cy="16" r="0.5" fill="currentColor"/>
                                <circle cx="16.5" cy="16" r="0.5" fill="currentColor"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-red-600 transition">Voir les voitures</h4>
                            <p class="text-sm text-gray-500 mt-1">Parcourir les voitures disponibles</p>
                        </div>
                    </a>

                    <a href="/mes-reservations" class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-red-200 transition flex items-start gap-4">
                        <div class="h-11 w-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-red-600 transition">Mes réservations</h4>
                            <p class="text-sm text-gray-500 mt-1">Suivre vos réservations en cours</p>
                        </div>
                    </a>

                @endif

            </div>

        </div>
    </div>
</x-app-layout>
