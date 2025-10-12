<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;

class DashboardControllerTest extends AdminTestCase
{
    public function test_admin_can_access_dashboard()
    {
        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertViewHas(['stats', 'recentPosts', 'recentComments']);
    }

    public function test_regular_user_cannot_access_dashboard()
    {
        $this->actingAsRegularUser()
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_guest_cannot_access_dashboard()
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_correct_stats()
    {
        // Создаем тестовые данные
        Post::factory()->count(3)->create();
        Category::factory()->count(2)->create();
        Comment::factory()->count(5)->create();
        Tag::factory()->count(4)->create();

        $this->actingAsAdmin()
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['posts_count'] >= 3 &&
                       $stats['categories_count'] >= 2 &&
                       $stats['comments_count'] >= 5 &&
                       $stats['tags_count'] >= 4 &&
                       $stats['users_count'] >= 2; // admin + regular user
            });
    }

    public function test_dashboard_shows_recent_posts()
    {
        $posts = Post::factory()->count(7)->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.dashboard'));

        $recentPosts = $response->viewData('recentPosts');
        $this->assertCount(5, $recentPosts); // Should show only 5 recent posts
    }

    public function test_dashboard_shows_recent_comments()
    {
        $comments = Comment::factory()->count(8)->create();

        $response = $this->actingAsAdmin()
            ->get(route('admin.dashboard'));

        $recentComments = $response->viewData('recentComments');
        $this->assertCount(5, $recentComments); // Should show only 5 recent comments
    }
}
