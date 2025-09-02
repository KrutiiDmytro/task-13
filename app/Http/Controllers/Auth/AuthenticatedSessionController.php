<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Показ форми логіну.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Обробка логіну з редіректом за роллю.
     *
     * Якщо користувач адмін — ведемо в адмінку (/admin).
     * Інакше — на головну (список постів).
     * Використовуємо intended, щоб поважати сторінку, куди користувач намагався потрапити до логіну.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();              // аутентифікація
        $request->session()->regenerate();     // захист від фіксації сесії

        $user = $request->user();

        if ($user && (bool)($user->is_admin ?? false)) {
            // адмін — у адмін-панель
            return redirect()->intended(route('admin.dashboard'));
        }

        // звичайний користувач — на головну
        return redirect()->intended(route('posts.index'));
    }

    /**
     * Логаут.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
