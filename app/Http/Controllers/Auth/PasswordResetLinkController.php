<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Affiche le formulaire "Mot de passe oublie".
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoie un email contenant un lien de reinitialisation du mot de passe.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
        ]);

        // Broker Laravel : genere un token, l'enregistre dans
        // password_reset_tokens et envoie l'email via la Notification
        // native ResetPassword du modele User.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
