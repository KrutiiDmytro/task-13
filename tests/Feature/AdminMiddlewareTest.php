<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['admin' => false]);
        
        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_routes(): void
    {
        $user = User::factory()->create(['admin' => true]);
        
        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->get('/admin')
            ->assertRedirect('/login');
    }
}