<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verified_user_is_redirected_to_dashboard(): void
    {
        // Создаем верифицированного пользователя
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        // Пользователь с подтвержденным email должен быть перенаправлен на dashboard
        // Покрывает строку 18 в EmailVerificationPromptController
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_unverified_user_sees_verification_prompt(): void
    {
        // Создаем неверифицированного пользователя
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        // Неверифицированный пользователь должен видеть страницу верификации
        // Покрывает строку 19 в EmailVerificationPromptController
        $response->assertStatus(200)
            ->assertViewIs('auth.verify-email');
    }

    public function test_already_verified_user_is_redirected_without_event(): void
    {
        // Создаем уже верифицированного пользователя
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // Пользователь уже верифицирован, поэтому должен быть перенаправлен сразу
        // Покрывает строку 18 в VerifyEmailController
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');

        // Событие Verified не должно быть отправлено, так как пользователь уже верифицирован
        Event::assertNotDispatched(Verified::class);

        // Пользователь остается верифицированным
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
