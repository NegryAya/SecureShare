<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord de l'utilisateur connecte.
     *
     * A ce stade (Sprint 1), la gestion des fichiers n'existe pas encore :
     * les statistiques sont donc calculees a partir de la relation
     * "files" du modele User, qui renverra 0 tant que le Sprint 2
     * (upload) n'est pas developpe. La structure est deja prete pour
     * afficher les vraies donnees des que la fonctionnalite existera.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

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
        ];

        return view('dashboard.index', compact('stats'));
    }
}
