<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        
        $middleware->handle($request, function () {
            return response('OK');
        });
    }

    public function test_guest_redirected_to_login()
    {
        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
    }
}