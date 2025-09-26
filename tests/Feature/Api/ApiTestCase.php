<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $baseUrl = '/api/v1';
    protected $resource = 'posts'; // Переопределяется в дочерних классах
    protected $user;

    protected function setUp(): void
{
    parent::setUp();
    $this->user = \App\Models\User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
}

    protected function getResourceUrl($id = null)
    {
        return $id 
            ? "{$this->baseUrl}/{$this->resource}/{$id}" 
            : "{$this->baseUrl}/{$this->resource}";
    }

    protected function createResourceData()
    {
        // Базовый метод, переопределяется в дочерних классах
        return [];
    }

    protected function assertResourceStructure($response)
    {
        $response->assertJsonStructure([
            'data' => [
                'id',
                'created_at',
                'updated_at'
            ]
        ]);
    }
}