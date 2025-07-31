<?php

namespace Tests\Feature;

// 1. Добавляем эту строку, чтобы импортировать трейт
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // 2. Добавляем эту строку, чтобы использовать трейт в нашем тесте
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
