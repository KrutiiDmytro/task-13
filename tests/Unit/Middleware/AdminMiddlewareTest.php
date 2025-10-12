<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_admin_users(): void
    {
        $admin = User::factory()->create(['admin' => true]);

        // Мокаем Auth::check() и Auth::user()
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($admin);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        $response = $middleware->handle($request, function ($request) {
            return new Response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function it_blocks_regular_users(): void
    {
        $user = User::factory()->create(['admin' => false]);

        // Мокаем Auth::check() и Auth::user()
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        // Ожидаем HttpException с кодом 403
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Недостаточно прав для доступа к этому разделу.');

        $middleware->handle($request, function ($request) {
            return new Response('OK');
        });
    }

    #[Test]
    public function it_blocks_guests(): void
    {
        // Мокаем Auth::check() для гостя
        Auth::shouldReceive('check')->once()->andReturn(false);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        $response = $middleware->handle($request, function ($request) {
            return new Response('OK');
        });

        // Гости перенаправляются на страницу логина (302)
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect());
    }

    #[Test]
    public function it_blocks_users_with_false_admin(): void
    {
        // Вместо null используем false
        $user = User::factory()->create(['admin' => false]);

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        // Ожидаем HttpException с кодом 403
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Недостаточно прав для доступа к этому разделу.');

        $middleware->handle($request, function ($request) {
            return new Response('OK');
        });
    }

    #[Test]
    public function it_blocks_users_without_admin_property(): void
    {
        // Создаем пользователя и тестируем случай, когда свойство не определено
        $user = new User();
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        // Не устанавливаем admin - будет null при обращении

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        // Ожидаем HttpException с кодом 403
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Недостаточно прав для доступа к этому разделу.');

        $middleware->handle($request, function ($request) {
            return new Response('OK');
        });
    }

    #[Test]
    public function middleware_can_be_instantiated(): void
    {
        $middleware = new AdminMiddleware();

        $this->assertInstanceOf(AdminMiddleware::class, $middleware);
    }

    #[Test]
    public function handle_method_exists(): void
    {
        $middleware = new AdminMiddleware();

        $this->assertTrue(method_exists($middleware, 'handle'));

        $reflection = new \ReflectionMethod($middleware, 'handle');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('next', $parameters[1]->getName());
    }

    #[Test]
    public function it_checks_status_code_from_exception(): void
    {
        $user = User::factory()->create(['admin' => false]);

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        try {
            $middleware->handle($request, function ($request) {
                return new Response('OK');
            });
            $this->fail('Expected HttpException was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            $this->assertEquals('Недостаточно прав для доступа к этому разделу.', $e->getMessage());
        }
    }

    #[Test]
    public function it_handles_admin_user_with_true_boolean(): void
    {
        $admin = User::factory()->create(['admin' => true]);

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($admin);

        $request = Request::create('/admin');
        $middleware = new AdminMiddleware();

        $nextCalled = false;
        $response = $middleware->handle($request, function ($request) use (&$nextCalled) {
            $nextCalled = true;

            return new Response('Admin Access Granted');
        });

        $this->assertTrue($nextCalled, 'Next middleware should be called for admin users');
        $this->assertEquals('Admin Access Granted', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function it_handles_different_admin_values(): void
    {
        // Тестируем разные значения, которые должны считаться "admin"
        $testCases = [
            ['value' => true, 'expected' => true, 'description' => 'boolean true'],
            ['value' => 1, 'expected' => true, 'description' => 'integer 1'],
            ['value' => '1', 'expected' => true, 'description' => 'string "1"'],
            ['value' => false, 'expected' => false, 'description' => 'boolean false'],
            ['value' => 0, 'expected' => false, 'description' => 'integer 0'],
            ['value' => '0', 'expected' => false, 'description' => 'string "0"'],
        ];

        foreach ($testCases as $testCase) {
            $user = User::factory()->create(['admin' => $testCase['value']]);

            Auth::shouldReceive('check')->once()->andReturn(true);
            Auth::shouldReceive('user')->once()->andReturn($user);

            $request = Request::create('/admin');
            $middleware = new AdminMiddleware();

            if ($testCase['expected']) {
                // Ожидаем успешное прохождение
                $response = $middleware->handle($request, function ($request) {
                    return new Response('OK');
                });
                $this->assertEquals(
                    200,
                    $response->getStatusCode(),
                    "Failed for {$testCase['description']}"
                );
            } else {
                // Ожидаем блокировку
                try {
                    $middleware->handle($request, function ($request) {
                        return new Response('OK');
                    });
                    $this->fail("Expected HttpException for {$testCase['description']}");
                } catch (HttpException $e) {
                    $this->assertEquals(
                        403,
                        $e->getStatusCode(),
                        "Wrong status code for {$testCase['description']}"
                    );
                }
            }
        }
    }
}
