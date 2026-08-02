<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    /**
     * Cette table n'a pas de colonne "updated_at" (voir migration).
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Actions journalisees reconnues par l'application.
     */
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_UPLOAD = 'upload';
    public const ACTION_DOWNLOAD = 'download';
    public const ACTION_DELETE = 'delete';
    public const ACTION_SHARE = 'share';
    public const ACTION_RENAME = 'rename';
    public const ACTION_REPLACE = 'replace';

    /**
     * Une entree de log appartient a un utilisateur (peut etre nulle).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Raccourci pour creer rapidement une entree de log depuis n'importe
     * quel controleur : Log::record('login', $userId).
     */
    public static function record(string $action, ?int $userId, ?string $ip = null): self
    {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => $ip ?? request()->ip(),
            'created_at' => now(),
        ]);
    }
}
