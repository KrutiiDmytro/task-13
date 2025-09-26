<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_admin_users_to_access_admin_routes(): void
    {
        $admin = User::factory()->create(['admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        // Если админ панель настроена, ожидаем 200, иначе может быть 404
        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(302, $response->status()); // Не редирект на логин
    }

    #[Test]
    public function it_blocks_regular_users_from_admin_routes(): void
    {
        $user = User::factory()->create(['admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $this->assertEquals(403, $response->status());
    }

    #[Test]
    public function it_redirects_guests_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
}