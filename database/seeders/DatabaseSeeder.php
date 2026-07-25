<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Execute les seeders de l'application.
     *
     * Cree un compte de demonstration pour tester rapidement
     * l'authentification et le dashboard sans passer par le formulaire
     * d'inscription.
     */
    public function run(): void
    {
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'SecureShare',
            'email' => 'admin@secureshare.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@secureshare.test',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}
