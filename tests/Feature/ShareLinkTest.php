<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\SharedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShareLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_generate_a_share_link(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('files.share', $file), [
            'expires_in' => '24h',
        ]);

        $response->assertRedirect(route('shared-links.index'));
        $this->assertDatabaseHas('shared_links', ['file_id' => $file->id]);

        $link = SharedLink::first();
        $this->assertNotNull($link->expires_at);
        $this->assertFalse($link->hasPassword());
    }

    public function test_another_user_cannot_share_someone_elses_file(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $file = File::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->post(route('files.share', $file), ['expires_in' => 'none'])
            ->assertForbidden();
    }

    public function test_anyone_can_view_a_valid_public_share_link(): void
    {
        $link = SharedLink::factory()->create();

        $this->get(route('share.show', $link->token))
            ->assertOk()
            ->assertSee($link->file->original_name);
    }

    public function test_expired_share_link_shows_expired_page(): void
    {
        $link = SharedLink::factory()->expired()->create();

        $this->get(route('share.show', $link->token))
            ->assertOk()
            ->assertSee('Lien expire');
    }

    public function test_password_protected_link_requires_password_before_download(): void
    {
        Storage::fake(File::DISK);
        $link = SharedLink::factory()->withPassword('secret123')->create();
        Storage::disk(File::DISK)->put($link->file->storage_path, 'contenu');

        // Sans mot de passe : la page demande le mot de passe, pas le fichier.
        $this->get(route('share.show', $link->token))
            ->assertOk()
            ->assertDontSee($link->file->original_name);

        // Telechargement direct refuse sans verification prealable.
        $this->get(route('share.download', $link->token))
            ->assertForbidden();

        // Mauvais mot de passe.
        $this->post(route('share.verify', $link->token), ['password' => 'wrong'])
            ->assertSessionHasErrors('password');

        // Bon mot de passe : la verification est memorisee en session.
        $this->post(route('share.verify', $link->token), ['password' => 'secret123'])
            ->assertRedirect(route('share.show', $link->token));

        $this->get(route('share.download', $link->token))
            ->assertOk();
    }

    public function test_download_increments_counter_and_is_logged(): void
    {
        Storage::fake(File::DISK);
        $link = SharedLink::factory()->create();
        Storage::disk(File::DISK)->put($link->file->storage_path, 'contenu');

        $this->get(route('share.download', $link->token))->assertOk();

        $this->assertEquals(1, $link->fresh()->downloads);
        $this->assertDatabaseHas('logs', ['action' => 'download', 'user_id' => null]);
    }
}
