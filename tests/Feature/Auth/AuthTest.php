<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL_TEST_EXAMPLE = 'test@example.com';

    private const DASHBORD = '/dashboard';

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => self::EMAIL_TEST_EXAMPLE,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect(self::DASHBORD);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => self::EMAIL_TEST_EXAMPLE,
        ]);
    }

    public function test_user_can_login()
    {
        User::factory()->create([
            'email' => self::EMAIL_TEST_EXAMPLE,
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => self::EMAIL_TEST_EXAMPLE,
            'password' => 'password',
        ]);

        $response->assertRedirect(self::DASHBORD);
        $this->assertAuthenticated();
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get(self::DASHBORD);

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_protected_routes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(self::DASHBORD);

        $response->assertStatus(200);
    }
}
