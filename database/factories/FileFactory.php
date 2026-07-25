<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['pdf', 'docx', 'xlsx', 'jpg', 'png', 'zip']);
        $uuid = Str::uuid()->toString();

        return [
            'user_id' => User::factory(),
            'original_name' => fake()->word().'.'.$extension,
            'stored_name' => $uuid.'.'.$extension,
            'extension' => $extension,
            'mime_type' => 'application/octet-stream',
            'size' => fake()->numberBetween(1024, 5242880),
            'storage_path' => 'files/1/'.$uuid.'.'.$extension,
        ];
    }
}
