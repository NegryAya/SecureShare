<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_activity_log(): void
    {
        $this->get('/activity')->assertRedirect('/login');
    }

    public function test_user_only_sees_their_own_logs(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Log::record(Log::ACTION_LOGIN, $user->id, '127.0.0.1');
        Log::record(Log::ACTION_UPLOAD, $otherUser->id, '10.0.0.1');

        $response = $this->actingAs($user)->get('/activity');

        $response->assertOk();
        $response->assertSee('127.0.0.1');
        $response->assertDontSee('10.0.0.1');
    }
}
