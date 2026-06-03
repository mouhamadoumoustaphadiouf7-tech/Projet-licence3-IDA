<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $reservation = new Reservation();
        $reservation->date_debut = $request->date_debut;
        $reservation->date_fin = $request->date_fin;
        $reservation->id_utilisateur = $request->id_utilisateur;
        $reservation->id_vehicule = $request->id_vehicule;
        $reservation->statut_reservation = 'Confirmée';
        $reservation->save();

        return redirect()->back();
    }

    public function modifier(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        $reservation->date_debut = $request->date_debut;
        $reservation->date_fin = $request->date_fin;
        $reservation->save();

        return redirect()->back();
    }

    public function annuler($id)
    {
        $reservation = Reservation::find($id);
        $reservation->statut_reservation = 'Annulée';
        $reservation->save();

        return redirect()->back();
    }
    
    public function historique($id_utilisateur)
    {
        $reservations = Reservation::where('id_utilisateur', $id_utilisateur)->get();
        return response()->json($reservations);
    }
}