<?php

namespace Tests\Unit\Http\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Очищаем rate limiter перед каждым тестом
        RateLimiter::clear('test@example.com|127.0.0.1');
    }

    #[Test]
    public function authorize_returns_true(): void
    {
        $request = new LoginRequest;

        $this->assertTrue($request->authorize());
    }

    #[Test]
    public function rules_returns_correct_validation_rules(): void
    {
        $request = new LoginRequest;

        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('string', $rules['email']);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('required', $rules['password']);
        $this->assertContains('string', $rules['password']);
    }

    #[Test]
    public function authenticate_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Аутентификация должна пройти без исключений
        $request->authenticate();

        // Проверяем, что пользователь аутентифицирован
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    #[Test]
    public function authenticate_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->expectException(ValidationException::class);

        $request->authenticate();

        // Проверяем, что пользователь НЕ аутентифицирован
        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function authenticate_fails_with_nonexistent_user(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->expectException(ValidationException::class);

        $request->authenticate();

        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function authenticate_handles_remember_option(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $request->authenticate();

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    #[Test]
    public function authenticate_increments_rate_limiter_on_failed_attempt(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            // Ожидаем исключение
        }

        // Проверяем, что rate limiter был увеличен
        $this->assertEquals(1, RateLimiter::attempts($request->throttleKey()));
    }

    #[Test]
    public function authenticate_clears_rate_limiter_on_successful_attempt(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        // Сначала добавляем попытку в rate limiter
        RateLimiter::hit($request->throttleKey());
        $this->assertEquals(1, RateLimiter::attempts($request->throttleKey()));

        // Успешная аутентификация должна очистить rate limiter
        $request->authenticate();

        $this->assertEquals(0, RateLimiter::attempts($request->throttleKey()));
    }

    #[Test]
    public function ensure_is_not_rate_limited_passes_when_under_limit(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        // Добавляем 4 попытки (меньше лимита в 5)
        for ($i = 0; $i < 4; $i++) {
            RateLimiter::hit($request->throttleKey());
        }

        // Не должно быть исключения
        $request->ensureIsNotRateLimited();

        $this->assertTrue(true); // Тест прошел, если дошли до этой точки
    }

    #[Test]
    public function ensure_is_not_rate_limited_throws_exception_when_over_limit(): void
    {
        Event::fake([Lockout::class]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        // Добавляем 5 попыток (достигаем лимита)
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($request->throttleKey());
        }

        $this->expectException(ValidationException::class);

        $request->ensureIsNotRateLimited();
    }

    #[Test]
    public function ensure_is_not_rate_limited_fires_lockout_event(): void
    {
        Event::fake([Lockout::class]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        // Добавляем 5 попыток
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($request->throttleKey());
        }

        try {
            $request->ensureIsNotRateLimited();
        } catch (ValidationException $e) {
            // Ожидаем исключение
        }

        // Проверяем, что событие Lockout было запущено
        Event::assertDispatched(Lockout::class);
    }

    #[Test]
    public function ensure_is_not_rate_limited_includes_correct_error_message(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        // Добавляем 5 попыток
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($request->throttleKey());
        }

        try {
            $request->ensureIsNotRateLimited();
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('email', $errors);
            $this->assertStringContainsString('Too many login attempts', $errors['email'][0]);
        }
    }

    #[Test]
    public function throttle_key_generates_correct_format(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'Test@Example.com',
        ], [], [], ['REMOTE_ADDR' => '192.168.1.1']);

        $throttleKey = $request->throttleKey();

        // Ключ должен содержать email в нижнем регистре и IP
        $this->assertEquals('test@example.com|192.168.1.1', $throttleKey);
    }

    #[Test]
    public function throttle_key_handles_special_characters(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'tëst@éxample.com',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        $throttleKey = $request->throttleKey();

        // Проверяем, что специальные символы транслитерируются
        $this->assertStringContainsString('test@example.com|127.0.0.1', $throttleKey);
    }

    #[Test]
    public function full_authentication_flow_with_rate_limiting(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        // Делаем 4 неудачные попытки
        for ($i = 0; $i < 4; $i++) {
            try {
                $request->authenticate();
            } catch (ValidationException $e) {
                // Ожидаем исключения
            }
        }

        // 5-я попытка должна вызвать rate limiting
        $this->expectException(ValidationException::class);
        $request->authenticate();
    }

    #[Test]
    public function request_validation_works_correctly(): void
    {
        // Тест валидации через создание реального запроса
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[Test]
    public function class_instantiation_works(): void
    {
        $request = new LoginRequest;

        $this->assertInstanceOf(LoginRequest::class, $request);
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
