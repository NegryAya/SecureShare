<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "logs" : journal d'audit de securite.
     *
     * Enregistre chaque action sensible (login, logout, upload, download,
     * delete, share) avec l'utilisateur concerne, l'adresse IP et la date.
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();

            // nullable() : certaines actions (tentative de login echouee)
            // peuvent survenir sans utilisateur authentifie connu.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Action realisee : login, logout, upload, download, delete, share
            $table->string('action');

            $table->string('ip_address', 45)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
