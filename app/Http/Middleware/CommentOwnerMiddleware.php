<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Comment;
use Symfony\Component\HttpFoundation\Response;

class CommentOwnerMiddleware
{
    /**
     * Проверяет права на редактирование комментария
     */
    public function handle(Request $request, Closure $next): Response
    {
        $comment = $request->route('comment');
        
        if (!$comment instanceof Comment) {
            return $next($request);
        }
        
        $user = auth()->user();
        
        if (!$user) {
            abort(403, 'Необходимо войти в систему');
        }
        
        // Администратор может редактировать любые комментарии
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Пользователь может редактировать только свои комментарии
        if ($comment->author !== $user->name) {
            abort(403, 'У вас нет прав для выполнения этого действия с комментарием');
        }
        
        return $next($request);
    }
}