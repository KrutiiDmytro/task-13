<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        
        // Проверяем является ли пользователь администратором
        if ($user->isAdmin()) {
            try {
                return redirect()->intended(route('admin.dashboard'));
            } catch (\Exception $e) {
                // Fallback если админ маршрут не работает
                return redirect()->intended('/');
            }
        }

        // Обычные пользователи идут на главную страницу
        return redirect()->intended(route('posts.index'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Запоминаем, был ли пользователь администратором
        $wasAdmin = auth()->check() && auth()->user()->isAdmin();
        
        Auth::guard('web')->logout();

        // Полная очистка сессии
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();

        // Очистить все данные формы
        $request->session()->forget('_old_input');
        $request->session()->forget('_flash');
        
        // Создаем response с заголовками против кэширования
        $response = redirect('/login')
            ->with('logout_success', 'Вы успешно вышли из системы')
            ->with('clear_form', true);
            
        // Добавляем заголовки против кэширования
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        
        return $response;
    }
}
