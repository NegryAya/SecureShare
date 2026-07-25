<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SharedLink>
 */
class SharedLinkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id' => File::factory(),
            'token' => Str::random(40),
            'password' => null,
            'expires_at' => null,
            'downloads' => 0,
            'created_at' => now(),
        ];
    }

    /**
     * Etat : lien protege par un mot de passe (en clair : "secret123").
     */
    public function withPassword(string $password = 'secret123'): static
    {
        return $this->state(fn () => [
            'password' => bcrypt($password),
        ]);
    }

    /**
     * Etat : lien deja expire.
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
