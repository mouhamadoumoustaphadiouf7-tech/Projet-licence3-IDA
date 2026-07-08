<?php

namespace App\Http\Controllers;
use App\Models\Voiture;

use Illuminate\Http\Request;

class VoitureController extends Controller
{
    //


    public function create()
    {
        return view('voitures.create');
    }
    public function edit($id)
    {
        $voiture = Voiture::findOrFail($id);

        if (!in_array(auth()->user()->role, ['vendeur', 'admin'])) {
            return redirect('/voitures')->with('error', "Accès refusé");
        }

        // vendeur ne modifie QUE ses voitures
        if (auth()->user()->role == 'vendeur' && $voiture->user_id != auth()->id()) {
            return redirect('/voitures')->with('error', "Ce n'est pas votre voiture");
        }

        return view('voitures.edit', compact('voiture'));
    }
    public function update(Request $request, $id)
    {
        $voiture = Voiture::findOrFail($id);

        if (!in_array(auth()->user()->role, ['vendeur', 'admin'])) {
            return redirect('/voitures')->with('error', "Accès refusé");
        }

        if (auth()->user()->role == 'vendeur' && $voiture->user_id != auth()->id()) {
            return redirect('/voitures')->with('error', "Ce n'est pas votre voiture");
        }

        $voiture->update($request->all());

        return redirect('/voitures')->with('success', 'Voiture modifiée');
    }
    public function destroy($id)
    {
        $voiture = Voiture::findOrFail($id);

        if (!in_array(auth()->user()->role, ['vendeur', 'admin'])) {
            return redirect('/voitures')->with('error', "Accès refusé");
        }

        if (auth()->user()->role == 'vendeur' && $voiture->user_id != auth()->id()) {
            return redirect('/voitures')->with('error', "Ce n'est pas votre voiture");
        }

        $voiture->delete();

        return redirect('/voitures')->with('success', 'Voiture supprimée');
    }
    public function show($id)
    {
        $voiture = Voiture::findOrFail($id);

        return view('voitures.show', compact('voiture'));
    }
    public function index(Request $request)
    {
        $query = Voiture::query();

        // Recherche texte (marque ou modèle)
        if ($request->search) {
            $query->where('marque', 'LIKE', '%' . $request->search . '%')
                ->orWhere('modele', 'LIKE', '%' . $request->search . '%');
        }

        // Filtre type
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Filtre prix max
        if ($request->prix_max) {
            $query->where('prix', '<=', $request->prix_max);
        }

        $voitures = $query->get();

        return view('voitures.index', compact('voitures'));
    }

    public function store(Request $request)
    {
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('voitures', 'public');
        }

        Voiture::create([
            'marque' => $request->marque,
            'modele' => $request->modele,
            'prix' => $request->prix,
            'description' => $request->description,
            'type' => $request->type,
            'statut' => 'disponible',
            'image' => $imagePath,
            'user_id' => auth()->id()
        ]);

        return redirect('/voitures');
    }
}
