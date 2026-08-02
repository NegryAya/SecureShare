<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Planification (Sprint 3)
|--------------------------------------------------------------------------
| Purge quotidienne des liens de partage expires. Necessite que le
| planificateur Laravel tourne en arriere-plan sur le serveur :
|   * * * * * php artisan schedule:run >> /dev/null 2>&1
| (a ajouter au crontab du serveur de production).
*/
Schedule::command('shared-links:prune')->daily();
