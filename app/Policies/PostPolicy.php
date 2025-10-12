<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Все могут просматривать посты
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Post $post): bool
    {
        return true; // Все могут просматривать отдельные посты
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user !== null; // Только аутентифицированные пользователи могут создавать посты
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Post $post): bool
    {
        if ($user === null) {
            return false;
        }

        // Админы могут редактировать любые посты
        if ($user->admin) {
            return true;
        }

        // Пользователи могут редактировать только свои посты
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Post $post): bool
    {
        if ($user === null) {
            return false;
        }

        // Админы могут удалять любые посты
        if ($user->admin) {
            return true;
        }

        // Пользователи могут удалять только свои посты
        return $post->user_id === $user->id;
    }
}
