<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class SharedLink extends Model
{
    use HasFactory;

    /**
     * Cette table n'a pas de colonne "updated_at" (voir migration Sprint 1).
     */
    public $timestamps = false;

    protected $fillable = [
        'file_id',
        'token',
        'password',
        'expires_at',
        'downloads',
        'created_at',
    ];

    /**
     * Le mot de passe ne doit jamais apparaitre dans un toArray()/toJson().
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
            'downloads' => 'integer',
        ];
    }

    /**
     * Un lien de partage appartient a un fichier.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Le lien est-il expire (date d'expiration passee) ?
     * Un lien sans date d'expiration (expires_at = null) n'expire jamais.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Le lien est-il encore valide (non expire) ?
     */
    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Le lien est-il protege par un mot de passe ?
     */
    public function hasPassword(): bool
    {
        return ! empty($this->password);
    }

    /**
     * Verifie qu'un mot de passe en clair correspond au hash stocke.
     */
    public function checkPassword(?string $password): bool
    {
        if (! $this->hasPassword()) {
            return true;
        }

        return $password !== null && Hash::check($password, $this->password);
    }

    /**
     * Incremente le compteur de telechargements du lien.
     */
    public function incrementDownloads(): void
    {
        $this->increment('downloads');
    }

    /**
     * URL publique complete du lien de partage.
     */
    public function getUrlAttribute(): string
    {
        return route('share.show', $this->token);
    }
}
