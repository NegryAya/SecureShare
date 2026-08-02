<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Affiche l'historique d'activite de l'utilisateur connecte.
     *
     * Chaque utilisateur ne voit que ses propres logs (filtre sur
     * user_id), conformement a la regle "chaque utilisateur ne peut
     * acceder qu'a ses propres donnees".
     */
    public function index(Request $request): View
    {
        $logs = $request->user()
            ->logs()
            ->latest('created_at')
            ->paginate(15);

        return view('activity.index', compact('logs'));
    }
}
