<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool // NOSONAR
    {
        return true; // Все могут просматривать посты
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Post $post): bool // NOSONAR
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
     * Проверяет, может ли пользователь выполнить действие с постом
     * (редактировать или удалять)
     *
     * @param  User|null  $user  Пользователь
     * @param  Post  $post  Пост
     * @return bool true если пользователь может выполнить действие
     */
    protected function canModifyPost(?User $user, Post $post): bool
    {
        if ($user === null) {
            return false;
        }

        // Админы могут редактировать/удалять любые посты
        if ($user->admin) {
            return true;
        }

        // Пользователи могут редактировать/удалять только свои посты
        return $post->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Post $post): bool
    {
        return $this->canModifyPost($user, $post);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Post $post): bool
    {
        return $this->canModifyPost($user, $post);
    }
}
