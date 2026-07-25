<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Log as UserLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Traite une demande d'inscription et connecte automatiquement
     * le nouvel utilisateur.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            // Hash::make() applique bcrypt (voir config/hashing + BCRYPT_ROUNDS).
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        Auth::login($user);

        UserLog::record(UserLog::ACTION_LOGIN, $user->id);

        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Bienvenue, votre compte a ete cree avec succes.');
    }
}
