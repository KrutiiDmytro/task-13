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

    // === Новые тесты для покрытия метода homePage ===

    #[Test]
    public function home_page_controller_method_returns_home_view()
    {
        // Создаем экземпляр контроллера
        $controller = new \App\Http\Controllers\PublicController();
        
        // Вызываем метод homePage напрямую
        $response = $controller->homePage();
        
        // Проверяем, что возвращается view
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        
        // Проверяем имя view более безопасным способом
        $viewName = $response->name();
        $this->assertEquals('home', $viewName);
    }

    #[Test]
    public function contact_page_controller_method_returns_contact_view()
    {
        // Создаем экземпляр контроллера
        $controller = new \App\Http\Controllers\PublicController();
        
        // Вызываем метод contactPage напрямую
        $response = $controller->contactPage();
        
        // Проверяем, что возвращается view
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        
        // Проверяем имя view
        $viewName = $response->name();
        $this->assertEquals('contact', $viewName);
    }

    #[Test]
    public function news_details_controller_method_returns_correct_data()
    {
        // Создаем экземпляр контроллера
        $controller = new \App\Http\Controllers\PublicController();
        
        // Вызываем метод newsDatailsPage напрямую
        $response = $controller->newsDatailsPage(1, 1);
        
        // Проверяем, что возвращается view
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        
        // Проверяем имя view
        $viewName = $response->name();
        $this->assertEquals('news-details', $viewName);
        
        // Проверяем данные
        $data = $response->getData();
        $this->assertEquals('First news', $data['data']['title']);
        $this->assertEquals('News', $data['category']['category']);
    }

    #[Test]
    public function news_details_controller_method_with_invalid_id_returns_not_found()
    {
        // Создаем экземпляр контроллера
        $controller = new \App\Http\Controllers\PublicController();
        
        // Вызываем метод newsDatailsPage с несуществующим ID
        $response = $controller->newsDatailsPage(999, 1);
        
        // Проверяем данные
        $data = $response->getData();
        $this->assertEquals('Not found', $data['data']['title']);
        $this->assertEquals('News', $data['category']['category']);
    }

    #[Test]
    public function news_details_controller_method_with_invalid_category_returns_unknown()
    {
        // Создаем экземпляр контроллера
        $controller = new \App\Http\Controllers\PublicController();
        
        // Вызываем метод newsDatailsPage с несуществующей категорией
        $response = $controller->newsDatailsPage(1, 999);
        
        // Проверяем данные
        $data = $response->getData();
        $this->assertEquals('First news', $data['data']['title']);
        $this->assertEquals('Unknown', $data['category']['category']);
    }
}