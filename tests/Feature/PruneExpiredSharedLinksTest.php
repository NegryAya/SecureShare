<?php

namespace Tests\Feature;

use App\Models\SharedLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneExpiredSharedLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_only_expired_links(): void
    {
        $expired = SharedLink::factory()->expired()->create();
        $active = SharedLink::factory()->create(['expires_at' => now()->addDay()]);
        $neverExpires = SharedLink::factory()->create(['expires_at' => null]);

        $this->artisan('shared-links:prune')
            ->expectsOutputToContain('1 lien(s) de partage expire(s) supprime(s).')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('shared_links', ['id' => $expired->id]);
        $this->assertDatabaseHas('shared_links', ['id' => $active->id]);
        $this->assertDatabaseHas('shared_links', ['id' => $neverExpires->id]);
    }
}
