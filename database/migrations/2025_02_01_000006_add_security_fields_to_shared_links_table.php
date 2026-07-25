<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sprint 2 : enrichit la table "shared_links" (creee au Sprint 1) avec
     * les fonctionnalites de partage securise :
     * - password   : mot de passe optionnel (haché) protegeant le lien
     * - expires_at : date d'expiration automatique du lien
     * - downloads  : compteur du nombre de telechargements via ce lien
     *
     * On utilise une migration separee (et non une modification de la
     * migration du Sprint 1) afin de respecter l'historique des sprints,
     * comme le ferait une vraie equipe de developpement.
     */
    public function up(): void
    {
        Schema::table('shared_links', function (Blueprint $table) {
            $table->string('password')->nullable()->after('token');
            $table->timestamp('expires_at')->nullable()->after('password');
            $table->unsignedInteger('downloads')->default(0)->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('shared_links', function (Blueprint $table) {
            $table->dropColumn(['password', 'expires_at', 'downloads']);
        });
    }
};
