<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Проверяет, является ли пользователь администратором
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем авторизацию
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Необходимо войти в систему');
        }

        $user = auth()->user();

        // Проверяем права администратора
        if (!$this->isAdmin($user)) {
            abort(403, 'У вас нет прав доступа к админ-панели');
        }

        return $next($request);
    }

    /**
     * Проверяет, является ли пользователь администратором
     */
    private function isAdmin($user): bool
    {
        // Проверка через поле is_admin
        if ($user->is_admin ?? false) {
            return true;
        }

        // Проверка через роли Spatie Permission
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        return false;
    }
}