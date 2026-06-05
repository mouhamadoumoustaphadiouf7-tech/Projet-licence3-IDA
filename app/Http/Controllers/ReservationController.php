<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'date_debut'     => 'required',
            'date_fin'       => 'required',
            'id_utilisateur' => 'required',
            'id_vehicule'    => 'required',
        ]);
        $donnees['statut_reservation'] = 'Confirmée';

      
        Reservation::create($donnees);

        return redirect()->back();
    }

    public function modifier(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        
        $reservation->update([
            'date_debut' => $request->date_debut,
            'date_fin'   => $request->date_fin,
        ]);

        return redirect()->back();
    }

    public function annuler($id)
    {
        Reservation::findOrFail($id)->update([
            'statut_reservation' => 'Annulée'
        ]);

        return redirect()->back();
    }
    
    public function historique($id_utilisateur)
    {
        $reservations = Reservation::where('id_utilisateur', $id_utilisateur)->get();
        return response()->json($reservations);
    }
}