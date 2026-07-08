<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // ✅ ICI (IMPORTANT)

class UserController extends Controller
{
    public function index()
    {
        if (auth()->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', "Vous n'avez pas accès à cette page");
        }

        $users = User::all();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (auth()->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', "Vous n'avez pas accès à cette page");
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', "Accès refusé");
        }

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role' => 'required|in:client,vendeur'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // ✅ marche maintenant
            'role' => $request->role
        ]);

        return redirect('/users')->with('success', 'Utilisateur ajouté');
    }
    public function edit($id)
    {
        if (auth()->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', "Accès refusé");
        }

        $user = User::findOrFail($id);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', "Accès refusé");
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|in:client,vendeur,admin'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ]);

        return redirect('/users')->with('success', 'Utilisateur modifié');
    }

    public function destroy($id)
    {
        if (auth()->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', "Accès refusé");
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect('/users')->with('success', 'Utilisateur supprimé');
    }
}
