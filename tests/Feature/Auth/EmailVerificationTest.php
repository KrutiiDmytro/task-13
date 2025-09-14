<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    }

    public function test_verified_user_redirected_from_verification_prompt(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_guest_cannot_access_verification_prompt(): void
    {
        $response = $this->get('/verify-email');

        $response->assertRedirect('/login');
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'wrong-hash']
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    // === Тесты для EmailVerificationNotificationController ===

    public function test_user_can_resend_verification_email(): void
    {
        $user = User::factory()->unverified()->create();

        Notification::fake();

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
    }

    public function test_verified_user_redirected_from_resend_verification(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_guest_cannot_resend_verification_email(): void
    {
        $response = $this->post('/email/verification-notification');

        $response->assertRedirect('/login');
    }

    public function test_resend_verification_email_is_throttled(): void
    {
        $user = User::factory()->unverified()->create();

        // Отправляем 6 запросов (лимит throttle:6,1)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($user)->post('/email/verification-notification');
            $response->assertSessionHas('status', 'verification-link-sent');
        }

        // 7-й запрос должен быть заблокирован
        $response = $this->actingAs($user)->post('/email/verification-notification');
        $response->assertStatus(429);
    }

    public function test_verification_notification_is_sent_when_resending(): void
    {
        $user = User::factory()->unverified()->create();

        Notification::fake();

        $this->actingAs($user)->post('/email/verification-notification');

        // Проверяем, что уведомление было отправлено
        Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    public function test_verification_notification_contains_correct_user(): void
    {
        $user = User::factory()->unverified()->create();

        Notification::fake();

        $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class,
            function ($notification, $channels, $notifiable) use ($user) {
                return $notifiable->id === $user->id;
            }
        );
    }
}