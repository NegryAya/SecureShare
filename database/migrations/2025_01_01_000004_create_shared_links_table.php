<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "shared_links" : lien public unique permettant de telecharger
     * un fichier sans authentification (fonctionnalite du Sprint 3).
     */
    public function up(): void
    {
        Schema::create('shared_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('file_id')
                ->constrained()
                ->cascadeOnDelete();

            // Jeton unique utilise dans l'URL /share/{token}
            $table->string('token', 64)->unique();

            $table->timestamp('created_at')->nullable();

            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_links');
    }
};
