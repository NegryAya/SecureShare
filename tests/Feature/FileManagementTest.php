<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_files_page(): void
    {
        $this->get('/files')->assertRedirect('/login');
    }

    public function test_a_user_can_upload_an_allowed_file(): void
    {
        Storage::fake(File::DISK);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/files', [
            'file' => UploadedFile::fake()->create('rapport.pdf', 500, 'application/pdf'),
        ]);

        $response->assertRedirect('/files');
        $this->assertDatabaseHas('files', [
            'user_id' => $user->id,
            'original_name' => 'rapport.pdf',
            'extension' => 'pdf',
        ]);

        $file = File::first();
        // Le nom stocke physiquement ne doit jamais etre le nom d'origine
        // (renomme en UUID).
        $this->assertNotEquals('rapport.pdf', $file->stored_name);
        Storage::disk(File::DISK)->assertExists($file->storage_path);
    }

    public function test_upload_rejects_disallowed_extension(): void
    {
        Storage::fake(File::DISK);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/files', [
            'file' => UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('files', 0);
    }

    public function test_upload_rejects_file_larger_than_20mb(): void
    {
        Storage::fake(File::DISK);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/files', [
            'file' => UploadedFile::fake()->create('large.pdf', 20481, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_owner_can_download_their_file(): void
    {
        Storage::fake(File::DISK);
        $user = User::factory()->create();

        $file = File::factory()->for($user)->create([
            'storage_path' => 'files/'.$user->id.'/test.pdf',
        ]);
        Storage::disk(File::DISK)->put($file->storage_path, 'contenu-test');

        $this->actingAs($user)
            ->get(route('files.download', $file))
            ->assertOk();
    }

    public function test_another_user_cannot_download_someone_elses_file(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $file = File::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->get(route('files.download', $file))
            ->assertForbidden();
    }

    public function test_owner_can_delete_their_file(): void
    {
        Storage::fake(File::DISK);
        $user = User::factory()->create();

        $file = File::factory()->for($user)->create([
            'storage_path' => 'files/'.$user->id.'/test.pdf',
        ]);
        Storage::disk(File::DISK)->put($file->storage_path, 'contenu-test');

        $this->actingAs($user)
            ->delete(route('files.destroy', $file))
            ->assertRedirect(route('files.index'));

        $this->assertDatabaseMissing('files', ['id' => $file->id]);
        Storage::disk(File::DISK)->assertMissing($file->storage_path);
    }

    public function test_another_user_cannot_delete_someone_elses_file(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $file = File::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->delete(route('files.destroy', $file))
            ->assertForbidden();

        $this->assertDatabaseHas('files', ['id' => $file->id]);
    }
}
