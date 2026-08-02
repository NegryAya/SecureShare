<?php

namespace App\Http\Controllers;

use App\Models\SharedLink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord de l'utilisateur connecte.
     *
     * Les statistiques sont calculees a partir des relations Eloquent de
     * l'utilisateur connecte uniquement (jamais celles d'un autre
     * utilisateur), et se completent automatiquement au fil des sprints :
     * fichiers (Sprint 2), liens de partage actifs/expires (Sprint 3).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Liens de partage crees pour les fichiers de l'utilisateur.
        $linksQuery = SharedLink::whereHas('file', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });

        $stats = [
            'files_count' => $user->files()->count(),
            'total_size' => $user->files()->sum('size'),
            'shared_count' => $user->files()
                ->whereHas('sharedLinks')
                ->count(),
            'recent_files' => $user->files()
                ->latest()
                ->take(5)
                ->get(),

            // Sprint 3 : statistiques detaillees sur les liens de partage.
            'links_total' => (clone $linksQuery)->count(),
            'links_active' => (clone $linksQuery)
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count(),
            'links_expired' => (clone $linksQuery)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->count(),
        ];

        return view('dashboard.index', compact('stats'));
    }
}
