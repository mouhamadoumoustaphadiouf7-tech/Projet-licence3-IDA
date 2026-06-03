<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
Route::get('/', function () {
    return view('welcome');
});
Route::post('/reservations/creer', [ReservationController::class, 'store']);
Route::post('/reservations/modifier/{id}', [ReservationController::class, 'modifier']);
Route::post('/reservations/annuler/{id}', [ReservationController::class, 'annuler']);
Route::get('/reservations/historique/{id_utilisateur}', [ReservationController::class, 'historique']);
