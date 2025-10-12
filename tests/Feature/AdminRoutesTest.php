<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_are_protected(): void
    {
        $admin = User::factory()->create(['admin' => true]);
        $regularUser = User::factory()->create(['admin' => false]);

        // Admin can access
        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get('/admin/posts')
            ->assertStatus(200);

        // Regular user cannot access
        $this->actingAs($regularUser)
            ->get('/admin')
            ->assertStatus(403);
    }
}
