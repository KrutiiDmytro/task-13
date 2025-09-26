<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    public function list(Request $request): LengthAwarePaginator
    {
        return Comment::with('post')->latest()->paginate(20)->withQueryString();
    }

    public function create(array $data): Comment
    {
        return Comment::create([
            'author_name' => $data['author'] ?? $data['author_name'] ?? null,
            'author_email'=> $data['author_email'] ?? null,
            'content'     => $data['content'],
            'post_id'     => $data['post_id'],
        ]);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update([
            'author_name' => $data['author'] ?? $data['author_name'] ?? $comment->author_name,
            'author_email'=> $data['author_email'] ?? $comment->author_email,
            'content'     => $data['content'] ?? $comment->content,
            'post_id'     => $data['post_id'] ?? $comment->post_id,
        ]);
        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}