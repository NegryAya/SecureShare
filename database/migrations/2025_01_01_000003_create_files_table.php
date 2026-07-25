<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "files" : represente un fichier appartenant a un utilisateur.
     *
     * Cette table est creee des le Sprint 1 pour preparer la base de
     * donnees complete du projet, mais aucune fonctionnalite d'upload
     * n'est implementee avant le Sprint 2.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();

            // Proprietaire du fichier. Si l'utilisateur est supprime,
            // ses fichiers le sont aussi (cascade).
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('original_name');   // Nom original du fichier upload par l'utilisateur
            $table->string('stored_name');      // Nom unique genere pour le stockage physique
            $table->string('extension', 10);    // Extension (pdf, docx, png, ...)
            $table->string('mime_type');        // Type MIME reel du fichier
            $table->unsignedBigInteger('size'); // Taille en octets
            $table->string('storage_path');     // Chemin relatif dans le disque de stockage

            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
