<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     *  Доступ лише для адміністратора.
     * Підтримує і сучасне поле is_admin, і старе admin (для сумісності з тестами).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAdmin = auth()->check() && (bool) (auth()->user()->is_admin ?? auth()->user()->admin ?? false);

        if (!$isAdmin) {
            abort(403, 'Доступ заборонено. Потрібні права адміністратора.');
        }

        return $next($request);
    }
}
