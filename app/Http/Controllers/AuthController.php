<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //  Afficher inscription
    public function afficherFormulaire()
    {
        return view('inscription');
    }

    //  Inscription
    public function inscrire(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|in:client,vendeur',
        ]);

        User::create([
            'name' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('login')
            ->with('success', 'Inscription réussie 🎉 Vous pouvez vous connecter.');
    }

    //  Afficher login
    public function afficherLogin()
    {
        return view('login');
    }

    //  Connexion + rôles
    public function connecter(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //  AUTH FIXÉ
        if (Auth::attempt($request->only('email', 'password'))) {

            $request->session()->regenerate();

            $user = Auth::user();

            // sécurité si user inexistant
            if (!$user) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Utilisateur introuvable']);
            }

            $role = strtolower($user->role); //  évite erreur de casse

            //  ADMIN
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Bienvenue Admin');
            }

            //  VENDEUR
            if ($role === 'vendeur') {
                return redirect()->route('vendeur.dashboard')
                    ->with('success', 'Bienvenue Vendeur');
            }

            //  CLIENT
            if ($role === 'client') {
                return redirect()->route('accueil.test')
                    ->with('success', 'Bienvenue Client');
            }

            //  rôle inconnu
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Rôle utilisateur invalide']);
        }

        //  login échoué
        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.'
        ]);
    }
}