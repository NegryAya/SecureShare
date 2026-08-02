<?php

namespace App\Console\Commands;

use App\Models\SharedLink;
use Illuminate\Console\Command;

/**
 * Supprime en base les liens de partage dont la date d'expiration est
 * depassee. Les liens sans expiration (expires_at = null) ne sont
 * jamais concernes.
 *
 * Usage manuel : php artisan shared-links:prune
 * Planifiee automatiquement tous les jours (voir routes/console.php).
 */
class PruneExpiredSharedLinks extends Command
{
    /**
     * @var string
     */
    protected $signature = 'shared-links:prune';

    /**
     * @var string
     */
    protected $description = 'Supprime les liens de partage expires de la base de donnees';

    public function handle(): int
    {
        $count = SharedLink::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("{$count} lien(s) de partage expire(s) supprime(s).");

        return self::SUCCESS;
    }
}
