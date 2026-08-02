<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\SharedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    // --- Renommage ---------------------------------------------------

    public function test_owner_can_rename_their_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create([
            'original_name' => 'ancien-nom.pdf',
            'extension' => 'pdf',
        ]);

        $response = $this->actingAs($user)->put(route('files.rename', $file), [
            'name' => 'nouveau-nom',
        ]);

        $response->assertRedirect(route('files.index'));
        $this->assertEquals('nouveau-nom.pdf', $file->fresh()->original_name);
    }

    public function test_another_user_cannot_rename_someone_elses_file(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $file = File::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->put(route('files.rename', $file), ['name' => 'hack'])
            ->assertForbidden();
    }

    public function test_rename_rejects_names_containing_a_slash(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create();

        $this->actingAs($user)
            ->put(route('files.rename', $file), ['name' => '../etc/passwd'])
            ->assertSessionHasErrors('name');
    }

    // --- Remplacement --------------------------------------------------

    public function test_owner_can_replace_their_file_content(): void
    {
        Storage::fake(File::DISK);
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create([
            'storage_path' => 'files/'.$user->id.'/old.pdf',
        ]);
        Storage::disk(File::DISK)->put($file->storage_path, 'ancien-contenu');
        $oldPath = $file->storage_path;

        $response = $this->actingAs($user)->put(route('files.replace', $file), [
            'file' => UploadedFile::fake()->create('nouveau.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $response->assertRedirect(route('files.index'));

        $file->refresh();
        $this->assertEquals('nouveau.docx', $file->original_name);
        $this->assertEquals('docx', $file->extension);
        Storage::disk(File::DISK)->assertExists($file->storage_path);
        Storage::disk(File::DISK)->assertMissing($oldPath);
    }

    public function test_replacing_a_file_keeps_the_same_id_so_share_links_stay_valid(): void
    {
        Storage::fake(File::DISK);
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create([
            'storage_path' => 'files/'.$user->id.'/old.pdf',
        ]);
        Storage::disk(File::DISK)->put($file->storage_path, 'contenu');
        $link = SharedLink::factory()->create(['file_id' => $file->id]);

        $this->actingAs($user)->put(route('files.replace', $file), [
            'file' => UploadedFile::fake()->create('nouveau.pdf', 200, 'application/pdf'),
        ]);

        $this->assertDatabaseHas('shared_links', ['id' => $link->id, 'file_id' => $file->id]);
    }

    public function test_another_user_cannot_replace_someone_elses_file(): void
    {
        Storage::fake(File::DISK);
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $file = File::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->put(route('files.replace', $file), [
                'file' => UploadedFile::fake()->create('x.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();
    }

    public function test_replace_rejects_disallowed_extension(): void
    {
        Storage::fake(File::DISK);
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create();

        $this->actingAs($user)
            ->put(route('files.replace', $file), [
                'file' => UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('file');
    }

    // --- Recherche / filtre / tri --------------------------------------

    public function test_user_can_search_files_by_name(): void
    {
        $user = User::factory()->create();
        File::factory()->for($user)->create(['original_name' => 'rapport-annuel.pdf']);
        File::factory()->for($user)->create(['original_name' => 'photo-vacances.jpg']);

        $response = $this->actingAs($user)->get(route('files.index', ['search' => 'rapport']));

        $response->assertOk();
        $response->assertSee('rapport-annuel.pdf');
        $response->assertDontSee('photo-vacances.jpg');
    }

    public function test_user_can_filter_files_by_type(): void
    {
        $user = User::factory()->create();
        File::factory()->for($user)->create(['original_name' => 'doc.pdf', 'extension' => 'pdf']);
        File::factory()->for($user)->create(['original_name' => 'image.jpg', 'extension' => 'jpg']);

        $response = $this->actingAs($user)->get(route('files.index', ['type' => 'jpg']));

        $response->assertOk();
        $response->assertSee('image.jpg');
        $response->assertDontSee('doc.pdf');
    }

    public function test_search_only_returns_the_current_users_files(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        File::factory()->for($user)->create(['original_name' => 'mon-fichier.pdf']);
        File::factory()->for($otherUser)->create(['original_name' => 'fichier-autre-user.pdf']);

        $response = $this->actingAs($user)->get(route('files.index', ['search' => 'fichier']));

        $response->assertSee('mon-fichier.pdf');
        $response->assertDontSee('fichier-autre-user.pdf');
    }

    // --- Dashboard : statistiques des liens -----------------------------

    public function test_dashboard_shows_share_link_statistics(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create();

        SharedLink::factory()->create(['file_id' => $file->id, 'expires_at' => null]);
        SharedLink::factory()->create(['file_id' => $file->id, 'expires_at' => now()->addDay()]);
        SharedLink::factory()->expired()->create(['file_id' => $file->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        // 3 liens generes, 2 actifs (dont 1 sans expiration), 1 expire.
        $response->assertViewHas('stats', function ($stats) {
            return $stats['links_total'] === 3
                && $stats['links_active'] === 2
                && $stats['links_expired'] === 1;
        });
    }

    public function test_dashboard_statistics_only_count_the_current_users_links(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherFile = File::factory()->for($otherUser)->create();
        SharedLink::factory()->create(['file_id' => $otherFile->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['links_total'] === 0;
        });
    }
}
