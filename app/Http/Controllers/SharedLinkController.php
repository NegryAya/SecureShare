<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateShareLinkRequest;
use App\Models\File;
use App\Models\Log;
use App\Models\SharedLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SharedLinkController extends Controller
{
    /**
     * Affiche la liste des liens de partage crees par l'utilisateur
     * connecte ("Fichiers partages").
     */
    public function index(Request $request): View
    {
        $sharedLinks = SharedLink::query()
            ->whereHas('file', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with('file')
            ->latest('created_at')
            ->paginate(10);

        return view('shared-links.index', compact('sharedLinks'));
    }

    /**
     * Genere un nouveau lien de partage securise pour un fichier.
     *
     * Securite :
     * - Token genere aleatoirement (Str::random), long (40 caracteres) et
     *   non devinable, utilise dans l'URL publique /share/{token}.
     * - Mot de passe optionnel, jamais stocke en clair (Hash::make).
     * - Expiration optionnelle (24h / 7 jours / jamais).
     */
    public function store(CreateShareLinkRequest $request, File $file): RedirectResponse
    {
        $this->authorize('share', $file);

        $validated = $request->validated();

        $expiresAt = match ($validated['expires_in']) {
            '24h' => now()->addDay(),
            '7d' => now()->addDays(7),
            default => null,
        };

        $sharedLink = SharedLink::create([
            'file_id' => $file->id,
            // 40 caracteres aleatoires : espace de recherche bien trop
            // grand pour etre devine par force brute.
            'token' => Str::random(40),
            'password' => filled($validated['password'] ?? null)
                ? Hash::make($validated['password'])
                : null,
            'expires_at' => $expiresAt,
            'downloads' => 0,
            'created_at' => now(),
        ]);

        Log::record(Log::ACTION_SHARE, $request->user()->id);

        return redirect()->route('shared-links.index')
            ->with('status', 'Lien de partage cree avec succes.')
            ->with('shared_link_url', $sharedLink->url);
    }

    /**
     * Revoque (supprime) un lien de partage. Seul le proprietaire du
     * fichier associe peut le faire.
     */
    public function destroy(Request $request, SharedLink $sharedLink): RedirectResponse
    {
        $this->authorize('share', $sharedLink->file);

        $sharedLink->delete();

        return redirect()->route('shared-links.index')
            ->with('status', 'Le lien de partage a ete revoque.');
    }
}
