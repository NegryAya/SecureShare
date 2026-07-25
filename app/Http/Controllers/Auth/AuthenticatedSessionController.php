<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Log as UserLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authentifie l'utilisateur et demarre une nouvelle session.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Valide les identifiants et applique le rate limiting anti brute-force.
        $request->authenticate();

        $request->session()->regenerate();

        UserLog::record(UserLog::ACTION_LOGIN, Auth::id());

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Deconnecte l'utilisateur et detruit la session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        UserLog::record(UserLog::ACTION_LOGOUT, Auth::id());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Vous avez ete deconnecte avec succes.');
    }
}
