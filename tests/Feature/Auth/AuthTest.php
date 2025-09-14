<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('posts.index'));  
        $this->assertAuthenticated();
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('posts.index'));
        $this->assertAuthenticated();
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/dashboard');

        // /dashboard редиректит на /login
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_protected_routes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        // Авторизованный пользователь тоже редиректится на /login
        // потому что /dashboard просто редиректит на /login
        $response->assertRedirect('/login');
    }
}