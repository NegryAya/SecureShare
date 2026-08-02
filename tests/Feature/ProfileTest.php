<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_user_can_view_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_user_can_update_their_information(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'first_name' => 'Nouveau',
            'last_name' => 'Nom',
            'email' => 'nouveau@example.com',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Nouveau',
            'email' => 'nouveau@example.com',
        ]);
    }

    public function test_user_cannot_take_an_email_already_used_by_someone_else(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_change_their_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
