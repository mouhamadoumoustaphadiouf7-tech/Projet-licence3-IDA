<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoitureController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard principal (inutile mais on garde)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Routes protégées
Route::middleware('auth')->group(function () {

    // PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // VOITURES
    Route::get('/voitures', [VoitureController::class, 'index']);
    Route::get('/voitures/create', [VoitureController::class, 'create']);
    Route::post('/voitures', [VoitureController::class, 'store']);
    Route::get('/voitures/{id}/edit', [VoitureController::class, 'edit']);
    Route::get('/voitures/{id}', [VoitureController::class, 'show']);
    Route::put('/voitures/{id}', [VoitureController::class, 'update']);
    Route::delete('/voitures/{id}', [VoitureController::class, 'destroy']);

    // RESERVATIONS
    Route::post('/voitures/{id}/reserver', [ReservationController::class, 'store']);
    Route::get('/mes-reservations', [ReservationController::class, 'index']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    Route::post('/reservations/{id}/valider', [ReservationController::class, 'valider']);
    Route::post('/reservations/{id}/refuser', [ReservationController::class, 'refuser']);

    // USERS (ADMIN)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/create', [UserController::class, 'create']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}/edit', [UserController::class, 'edit']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // DASHBOARDS PAR ROLE
    Route::get('/admin', function () {
        if (auth()->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', "Accès refusé");
        }
        return view('dashboard.admin');
    });

    Route::get('/vendeur', function () {
        if (auth()->user()->role != 'vendeur') {
            return redirect('/dashboard')->with('error', "Accès refusé");
        }
        return view('dashboard.vendeur');
    });

    Route::get('/client', function () {
        if (auth()->user()->role != 'client') {
            return redirect('/dashboard')->with('error', "Accès refusé");
        }
        return view('dashboard.client');
    });
});

require __DIR__.'/auth.php';
