<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Voiture;



class ReservationController extends Controller
{
    //
    public function store($id)
    {
        if (auth()->user()->role != 'client') {
            return back()->with('error', "Seuls les clients peuvent réserver");
        }

        $voiture = Voiture::findOrFail($id);

        $existe = Reservation::where('user_id', auth()->id())
            ->where('voiture_id', $id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Vous avez déjà réservé cette voiture');
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'voiture_id' => $id,
            'date_reservation' => now(), // ✅ AJOUT IMPORTANT
            'statut' => 'en_attente'
        ]);

        return back()->with('success', 'Réservation envoyée');
    }
    public function valider($id)
    {
        $reservation = \App\Models\Reservation::findOrFail($id);

        if (auth()->user()->role != 'vendeur') {
            return back()->with('error', "Accès refusé");
        }

        // vérifier que c’est SA voiture
        if ($reservation->voiture->user_id != auth()->id()) {
            return back()->with('error', "Ce n'est pas votre voiture");
        }

        $reservation->statut = 'validee';
        $reservation->save();

        return back()->with('success', 'Réservation validée');
    }
    public function refuser($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->voiture->user_id != auth()->id()) {
            return back()->with('error', "Accès refusé");
        }

        $reservation->update(['statut' => 'annulee']);


        return back()->with('success', 'Réservation refusée');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->role == 'admin') {
            $reservations = \App\Models\Reservation::with('voiture', 'user')->get();
        } elseif ($user->role == 'vendeur') {
            $reservations = \App\Models\Reservation::whereHas('voiture', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with('voiture', 'user')->get();
        } else {
            $reservations = \App\Models\Reservation::where('user_id', $user->id)
                ->with('voiture')->get();
        }

        return view('reservations.index', compact('reservations'));
    }
    public function destroy($id)
    {
        $reservation = \App\Models\Reservation::findOrFail($id);

        if ($reservation->user_id != auth()->id()) {
            return back()->with('error', "Accès refusé");
        }

        $reservation->delete();

        return back()->with('success', 'Réservation annulée');
    }
    }
