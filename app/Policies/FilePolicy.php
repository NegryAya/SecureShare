<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

/**
 * Regle d'autorisation centrale du Sprint 2 :
 * "Chaque utilisateur ne peut acceder qu'a ses propres fichiers."
 *
 * Utilisee dans les controleurs via $this->authorize('view', $file), etc.
 * Laravel decouvre automatiquement cette classe grace a la convention de
 * nommage (App\Models\File -> App\Policies\FilePolicy), et elle est aussi
 * enregistree explicitement dans AppServiceProvider pour plus de clarte.
 */
class FilePolicy
{
    /**
     * L'utilisateur peut-il voir/telecharger ce fichier ?
     */
    public function view(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * L'utilisateur peut-il partager ce fichier (creer un lien) ?
     */
    public function share(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    /**
     * L'utilisateur peut-il supprimer ce fichier ?
     */
    public function delete(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }
}
