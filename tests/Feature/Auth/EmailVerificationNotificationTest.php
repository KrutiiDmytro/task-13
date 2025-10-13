<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailVerificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function store_sends_verification_notification_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null, // Не верифицирован
        ]);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect()
            ->assertSessionHas('status', 'verification-link-sent');

        // Проверяем, что уведомление было отправлено
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    #[Test]
    public function store_redirects_to_dashboard_for_already_verified_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(), // Уже верифицирован
        ]);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect(route('dashboard'));

        // Проверяем, что уведомление НЕ было отправлено
        Notification::assertNothingSent();
    }

    #[Test]
    public function store_redirects_to_intended_url_for_verified_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Устанавливаем intended URL в сессии
        session(['url.intended' => '/profile']);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect('/profile');
    }

    #[Test]
    public function store_requires_authentication(): void
    {
        $response = $this->post('/email/verification-notification');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function store_uses_back_redirect_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Симулируем запрос с определенным referer
        $response = $this->actingAs($user)
            ->from('/email/verify')
            ->post('/email/verification-notification');

        $response->assertRedirect('/email/verify')
            ->assertSessionHas('status', 'verification-link-sent');
    }

    #[Test]
    public function store_works_with_different_user_states(): void
    {
        Notification::fake();

        // Тест с пользователем, у которого email_verified_at = null
        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($unverifiedUser)
            ->post('/email/verification-notification')
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($unverifiedUser, VerifyEmail::class);

        // Очищаем уведомления для следующего теста
        Notification::fake();

        // Тест с пользователем, у которого email_verified_at установлен
        $verifiedUser = User::factory()->create([
            'email_verified_at' => now()->subHour(),
        ]);

        $this->actingAs($verifiedUser)
            ->post('/email/verification-notification')
            ->assertRedirect(route('dashboard'));

        Notification::assertNothingSent();
    }

    #[Test]
    public function store_method_calls_user_methods_correctly(): void
    {
        // Создаем мок пользователя для проверки вызовов методов
        $user = $this->createMock(User::class);

        // Настраиваем мок для неверифицированного пользователя
        $user->expects($this->once())
            ->method('hasVerifiedEmail')
            ->willReturn(false);

        $user->expects($this->once())
            ->method('sendEmailVerificationNotification');

        // Аутентифицируем мок пользователя
        $this->actingAs($user);

        $response = $this->post('/email/verification-notification');

        $response->assertRedirect()
            ->assertSessionHas('status', 'verification-link-sent');
    }

    #[Test]
    public function store_method_handles_verified_user_correctly(): void
    {
        // Создаем мок пользователя для верифицированного случая
        $user = $this->createMock(User::class);

        $user->expects($this->once())
            ->method('hasVerifiedEmail')
            ->willReturn(true);

        // sendEmailVerificationNotification НЕ должен вызываться
        $user->expects($this->never())
            ->method('sendEmailVerificationNotification');

        $this->actingAs($user);

        $response = $this->post('/email/verification-notification');

        $response->assertRedirect(route('dashboard'));
    }

    #[Test]
    public function controller_can_be_instantiated(): void
    {
        $controller = new \App\Http\Controllers\Auth\EmailVerificationNotificationController;

        $this->assertInstanceOf(
            \App\Http\Controllers\Auth\EmailVerificationNotificationController::class,
            $controller
        );
    }

    #[Test]
    public function store_method_exists_and_returns_redirect_response(): void
    {
        $controller = new \App\Http\Controllers\Auth\EmailVerificationNotificationController;

        $this->assertTrue(method_exists($controller, 'store'));

        // Проверяем, что метод возвращает RedirectResponse
        $user = User::factory()->create(['email_verified_at' => null]);
        $request = \Illuminate\Http\Request::create('/email/verification-notification', 'POST');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $controller->store($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    #[Test]
    public function integration_test_with_real_user_flow(): void
    {
        Notification::fake();

        // Создаем пользователя и логинимся
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // Проверяем, что пользователь не верифицирован
        $this->assertFalse($user->hasVerifiedEmail());

        // Отправляем запрос на повторную отправку верификации
        $response = $this->post('/email/verification-notification');

        $response->assertRedirect()
            ->assertSessionHas('status', 'verification-link-sent');

        // Проверяем, что уведомление отправлено
        Notification::assertSentTo($user, VerifyEmail::class);

        // Симулируем верификацию пользователя
        $user->markEmailAsVerified();

        // Теперь повторный запрос должен редиректить на dashboard
        $response = $this->post('/email/verification-notification');

        $response->assertRedirect(route('dashboard'));
    }

    #[Test]
    public function store_handles_middleware_correctly(): void
    {
        // Проверяем, что middleware auth работает
        $response = $this->post('/email/verification-notification');
        $response->assertRedirect('/login');

        // Проверяем с аутентифицированным пользователем
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->post('/email/verification-notification');
        $response->assertRedirect()
            ->assertSessionHas('status');
    }
}
