<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_non_admin_cannot_access_dashboard()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_statistics()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Проверяем что статистика отображается (любые числа)
        $response->assertSee('Постов');
        $response->assertSee('Категорий');  
        $response->assertSee('Комментариев');
        $response->assertSee('Пользователей');
        
        // Проверяем структуру страницы
        $response->assertSee('Панель управления');
        $response->assertSee('Недавние посты');
        $response->assertSee('Недавние комментарии');
    }

    public function test_dashboard_shows_recent_posts_section()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Проверяем что секция постов есть
        $response->assertSee('Недавние посты');
        $response->assertSee('Заголовок');
        $response->assertSee('Автор');
        $response->assertSee('Дата');
    }

    public function test_dashboard_shows_recent_comments_section()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Проверяем что секция комментариев есть
        $response->assertSee('Недавние комментарии');
        $response->assertSee('Комментарий');
        $response->assertSee('Пост');
    }
}