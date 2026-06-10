<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d'accueil par défaut
Route::get('/', function () {
    return view('welcome');
});

// Routes pour l'Inscription
Route::get('/inscription', [AuthController::class, 'afficherFormulaire'])->name('inscription.afficher');
Route::post('/inscription', [AuthController::class, 'inscrire'])->name('inscription.traiter');

// Routes pour la Connexion (Login)
Route::get('/login', [AuthController::class, 'afficherLogin'])->name('login');
Route::post('/login', [AuthController::class, 'connecter'])->name('login.traiter');
// Route pour l'espace Admin
Route::get('/admin/dashboard', function () {
    return "Bienvenue sur le tableau de bord de l'Administrateur !";
})->name('admin.dashboard');

// Route pour l'espace Vendeur
Route::get('/vendeur/dashboard', function () {
    return "Bienvenue sur l'espace Vendeur !";
})->name('vendeur.dashboard');

// Route par défaut pour le Client (votre page de test actuelle)
Route::get('/accueil-test', function () {
    return view('welcome'); // Ou la vue de votre choix
})->name('accueil.test');

// Page de test de succès (protégée par session)
Route::get('/accueil-test', function () {
    return "<h1> Connexion réussie !</h1><p>Bienvenue dans l'application, votre session est active.</p>";
})->middleware('auth')->name('accueil.test');