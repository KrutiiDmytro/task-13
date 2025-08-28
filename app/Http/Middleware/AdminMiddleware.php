<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Пропускає лише адмінів.
     *
     * Логіка:
     * 1) Якщо гість — редірект на сторінку логіну.
     * 2) Якщо користувач залогінений, але не адмін — 403 Forbidden.
     * 3) Якщо адмін — пропускаємо далі.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Крок 1: якщо користувач не автентифікований — відправляємо на логін
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Крок 2: перевіряємо прапорець is_admin (каст до bool на випадок int/str)
        $user = Auth::user();

        // Важливо: якщо в БД поле зветься інакше (наприклад, 'isAdmin'),
        // зміни назву нижче відповідно.
        if (!(bool) ($user->is_admin ?? false)) {
            // 403 краще за редірект: так прозоро видно, що бракує прав доступу
            abort(403, 'Недостатньо прав для доступу до цього розділу.');
        }

        // Крок 3: користувач — адмін, пропускаємо запит далі
        return $next($request);
    }
}
