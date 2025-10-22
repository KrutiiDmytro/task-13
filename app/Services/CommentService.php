<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    /**
     * Получает список всех комментариев с пагинацией
     *
     * @return LengthAwarePaginator Пагинированный список комментариев
     */
    public function list(): LengthAwarePaginator
    {
        return Comment::with('post')->latest()->paginate(20)->withQueryString();
    }

    /**
     * Создает новый комментарий
     *
     * @param  array<string, mixed>  $data  Данные комментария
     * @return Comment Созданный комментарий
     */
    public function create(array $data): Comment
    {
        return Comment::create([
            'author_name' => $data['author'] ?? $data['author_name'] ?? null,
            'author_email' => $data['author_email'] ?? null,
            'content' => $data['content'],
            'post_id' => $data['post_id'],
        ]);
    }

    /**
     * Обновляет существующий комментарий
     *
     * @param  Comment  $comment  Комментарий для обновления
     * @param  array<string, mixed>  $data  Новые данные
     * @return Comment Обновленный комментарий
     */
    public function update(Comment $comment, array $data): Comment
    {
        $comment->update([
            'author_name' => $data['author'] ?? $data['author_name'] ?? $comment->author_name,
            'author_email' => $data['author_email'] ?? $comment->author_email,
            'content' => $data['content'] ?? $comment->content,
            'post_id' => $data['post_id'] ?? $comment->post_id,
        ]);

        return $comment;
    }

    /**
     * Удаляет комментарий
     *
     * @param  Comment  $comment  Комментарий для удаления
     */
    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
