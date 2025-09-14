<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PublicControllerTest extends TestCase
{
    #[Test]
    public function home_redirects_to_posts_index()
    {
        $response = $this->get('/home');

        $response->assertStatus(302);
        $response->assertRedirect(route('posts.index'));
    }

    #[Test]
    public function can_view_contact_page()
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertViewIs('contact');
    }

    #[Test]
    public function can_view_news_details_with_valid_id()
    {
        $response = $this->get('/news/1');

        $response->assertStatus(200);
        $response->assertViewIs('news-details');
        $response->assertViewHas('data');
        $response->assertViewHas('category');
    }

    #[Test]
    public function can_view_news_details_with_category()
    {
        $response = $this->get('/news/1/2');

        $response->assertStatus(200);
        $response->assertViewIs('news-details');
        
        $data = $response->viewData('data');
        $category = $response->viewData('category');
        
        $this->assertEquals('First news', $data['title']);
        $this->assertEquals('News IT', $category['category']);
    }

    #[Test]
    public function shows_not_found_for_invalid_news_id()
    {
        $response = $this->get('/news/999');

        $response->assertStatus(200);
        $response->assertViewIs('news-details');
        
        $data = $response->viewData('data');
        $this->assertEquals('Not found', $data['title']);
    }

    #[Test]
    public function shows_unknown_category_for_invalid_category_id()
    {
        $response = $this->get('/news/1/999');

        $response->assertStatus(200);
        $response->assertViewIs('news-details');
        
        $category = $response->viewData('category');
        $this->assertEquals('Unknown', $category['category']);
    }
}