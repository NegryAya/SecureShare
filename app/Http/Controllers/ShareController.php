<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\SharedLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controleur public (aucune authentification requise) qui gere l'acces
 * aux fichiers partages via un lien du type /share/{token}.
 */
class ShareController extends Controller
{
    /**
     * Cle de session utilisee pour retenir qu'un visiteur a deja saisi le
     * bon mot de passe pour un token donne (evite de le redemander a
     * chaque clic pendant la meme visite).
     */
    private function sessionKey(string $token): string
    {
        return 'share_verified_'.$token;
    }

    /**
     * Affiche la page publique du lien : formulaire de mot de passe si
     * necessaire, sinon les informations du fichier avec un bouton de
     * telechargement.
     */
    public function show(Request $request, string $token): View
    {
        $sharedLink = SharedLink::with('file')->where('token', $token)->firstOrFail();

        if ($sharedLink->isExpired()) {
            return view('share.expired');
        }

        $needsPassword = $sharedLink->hasPassword()
            && ! $request->session()->get($this->sessionKey($token), false);

        return view('share.show', [
            'sharedLink' => $sharedLink,
            'needsPassword' => $needsPassword,
        ]);
    }

    /**
     * Verifie le mot de passe saisi par le visiteur.
     */
    public function verifyPassword(Request $request, string $token): RedirectResponse
    {
        $sharedLink = SharedLink::where('token', $token)->firstOrFail();

        if ($sharedLink->isExpired()) {
            return redirect()->route('share.show', $token);
        }

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Veuillez saisir le mot de passe.',
        ]);

        if (! $sharedLink->checkPassword($request->input('password'))) {
            return back()->withErrors([
                'password' => 'Mot de passe incorrect.',
            ]);
        }

        // Mot de passe correct : on memorise la verification en session
        // pour la duree de la visite (pas de cookie persistant).
        $request->session()->put($this->sessionKey($token), true);

        return redirect()->route('share.show', $token);
    }

    /**
     * Telecharge le fichier associe au lien de partage.
     *
     * - Refuse si le lien est expire.
     * - Refuse si un mot de passe est requis et n'a pas ete verifie.
     * - Incremente le compteur de telechargements et journalise l'action.
     */
    public function download(Request $request, string $token): StreamedResponse
    {
        $sharedLink = SharedLink::with('file')->where('token', $token)->firstOrFail();

        abort_if($sharedLink->isExpired(), 410, 'Ce lien de partage a expire.');

        $verified = ! $sharedLink->hasPassword()
            || $request->session()->get($this->sessionKey($token), false);

        abort_unless($verified, 403, 'Mot de passe requis pour telecharger ce fichier.');

        $file = $sharedLink->file;

        $sharedLink->incrementDownloads();

        // Telechargement anonyme (via lien public) : aucun utilisateur
        // authentifie n'est associe a cette action dans le journal.
        Log::record(Log::ACTION_DOWNLOAD, null);

        return Storage::disk($file::DISK)->download($file->storage_path, $file->original_name);
    }
}
