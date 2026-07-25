<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Attributs assignables en masse (mass assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
    ];

    /**
     * Attributs a cacher lors de la serialisation (toArray/toJson).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversion automatique des types de colonnes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Un utilisateur possede plusieurs fichiers.
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Un utilisateur possede plusieurs entrees de log (historique d'actions).
     */
    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    /**
     * Indique si l'utilisateur est un administrateur (utilise a partir de la V2).
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Nom complet de l'utilisateur (prenom + nom).
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
