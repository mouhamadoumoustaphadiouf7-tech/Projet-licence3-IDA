<?php

namespace App\Http\Controllers;
use App\Models\Voiture;

use Illuminate\Http\Request;

class VoitureController extends Controller
{
    //
    public function index()
    {
        $voitures = Voiture::all();
        return view('voitures.index', compact('voitures'));
    }

    public function create()
    {
        return view('voitures.create');
    }

    public function store(Request $request)
    {
        Voiture::create([
            'marque' => $request->marque,
            'modele' => $request->modele,
            'prix' => $request->prix,
            'description' => $request->description,
            'type' => $request->type,
            'statut' => 'disponible',
            'user_id' => auth()->id()
        ]);

        return redirect('/voitures')->with('success', 'Voiture ajoutée');
    }
}
