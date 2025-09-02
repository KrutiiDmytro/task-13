<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_admin_panel_button()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('posts.index'));

        $response->assertStatus(200);
        $response->assertSee('Админ-панель');
        $response->assertSee(route('admin.dashboard'));
    }

    public function test_regular_user_does_not_see_admin_panel_button()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('posts.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Админ-панель');
    }

    public function test_guest_does_not_see_admin_panel_button()
    {
        $response = $this->get(route('posts.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Админ-панель');
    }
}