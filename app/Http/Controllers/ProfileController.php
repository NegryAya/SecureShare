<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Affiche la page "Profil" (informations + changement de mot de passe).
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Met a jour le prenom / nom / email de l'utilisateur connecte.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.edit')
            ->with('status', 'Vos informations ont ete mises a jour avec succes.');
    }

    /**
     * Change le mot de passe de l'utilisateur connecte.
     *
     * Necessite la saisie du mot de passe actuel (regle "current_password")
     * pour empecher qu'une session laissee ouverte permette a quelqu'un
     * d'autre de changer le mot de passe sans le connaitre.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Veuillez saisir votre mot de passe actuel.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')
            ->with('status', 'Votre mot de passe a ete change avec succes.');
    }
}
