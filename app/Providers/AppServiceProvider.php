<?php

namespace App\Providers;

use App\Models\File;
use App\Policies\FilePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services de l'application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Initialise les services de l'application (appele au demarrage).
     */
    public function boot(): void
    {
        // Sprint 2 : un utilisateur ne peut agir que sur ses propres fichiers.
        Gate::policy(File::class, FilePolicy::class);

        // L'interface utilise Bootstrap 5 : on aligne la pagination dessus.
        Paginator::useBootstrapFive();
    }
}
