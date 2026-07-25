<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'original_name',
        'stored_name',
        'extension',
        'mime_type',
        'size',
        'storage_path',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * Un fichier appartient a un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un fichier peut avoir plusieurs liens de partage.
     */
    public function sharedLinks()
    {
        return $this->hasMany(SharedLink::class);
    }

    /**
     * Disque de stockage utilise pour tous les fichiers utilisateurs.
     * "local" = disque prive (non accessible directement via une URL
     * publique) : tout telechargement passe obligatoirement par un
     * controleur qui verifie les autorisations.
     */
    public const DISK = 'local';

    /**
     * Taille du fichier formatee de maniere lisible (Ko / Mo / Go).
     */
    public function getHumanSizeAttribute(): string
    {
        $size = (int) $this->size;

        return match (true) {
            $size >= 1073741824 => number_format($size / 1073741824, 2).' Go',
            $size >= 1048576 => number_format($size / 1048576, 2).' Mo',
            $size >= 1024 => number_format($size / 1024, 1).' Ko',
            default => $size.' octets',
        };
    }

    /**
     * Dernier lien de partage encore actif (non expire) pour ce fichier,
     * s'il en existe un.
     */
    public function activeSharedLink()
    {
        return $this->sharedLinks()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->first();
    }
}
