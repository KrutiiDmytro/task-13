<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    #[Test]
    public function admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    #[Test]
    public function regular_user_cannot_access_dashboard()
    {
        $response = $this->actingAs($this->user)->get('/admin');
        
        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get('/admin');
        
        $response->assertRedirect('/login');
    }

    #[Test]
    public function dashboard_shows_correct_statistics()
    {
        // Создаем тестовые данные
        $category = Category::factory()->create();
        $posts = Post::factory()->count(3)->create(['category_id' => $category->id]);
        $comments = Comment::factory()->count(5)->create(['post_id' => $posts->first()->id]);
        
        // Создаем теги с уникальными именами
        $tag1 = Tag::factory()->create(['name' => 'unique-tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'unique-tag-2']);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['posts_count']);
        $this->assertEquals(1, $stats['categories_count']);
        $this->assertEquals(5, $stats['comments_count']);
        $this->assertGreaterThanOrEqual(2, $stats['users_count']);
        $this->assertEquals(2, $stats['tags_count']);
    }

    #[Test]
    public function dashboard_shows_recent_posts()
    {
        $category = Category::factory()->create();
        Post::factory()->count(7)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('recentPosts');
        
        $recentPosts = $response->viewData('recentPosts');
        $this->assertCount(5, $recentPosts); // Показывает только 5 последних
    }

    #[Test]
    public function dashboard_shows_recent_comments()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);
        Comment::factory()->count(7)->create(['post_id' => $post->id]);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewHas('recentComments');
        
        $recentComments = $response->viewData('recentComments');
        $this->assertCount(5, $recentComments); // Показывает только 5 последних
    }
}