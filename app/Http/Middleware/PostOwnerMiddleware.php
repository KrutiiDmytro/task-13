<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Post;
use Symfony\Component\HttpFoundation\Response;

class PostOwnerMiddleware
{
    /**
     * Перевіряє чи має користувач право редагувати пост
     * (власник поста або адміністратор)
     */
     public function handle(Request $request, Closure $next): Response
    {
        $post = $request->route('post');
        
        if (!$post instanceof Post) {
            return $next($request);
        }
        
        $user = auth()->user();
        
        if (!$user || !$user->canEditPost($post)) {
            abort(403, 'У вас нет прав для выполнения этого действия');
        }
        
        return $next($request);
    }
    
    /**
     * Перевіряє чи може користувач редагувати пост
     */
    private function canEditPost(Post $post): bool
    {
        $user = auth()->user();
        
        // Якщо користувач не авторизований
        if (!$user) {
            return false;
        }
        
        // Якщо це власник поста
        if ($user->id === $post->user_id) {
            return true;
        }
        
        // Якщо це адміністратор
        return $this->isAdmin($user);
    }
    
    /**
     * Перевіряє чи є користувач адміністратором
     */
    private function isAdmin($user): bool
    {
        return $user && (
            ($user->is_admin ?? false) || 
            (method_exists($user, 'hasRole') && $user->hasRole('admin'))
        );
    }
}