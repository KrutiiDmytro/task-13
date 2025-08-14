<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommentController extends Controller
{
    // (UA) Список коментарів з пагінацією
    public function index(): View
    {
        $comments = Comment::with('post')->latest()->paginate(20);
        return view('comments.index', compact('comments'));
    }

    // (UA) Форма створення
    public function create(): View
    {
        $posts = Post::orderBy('title')->get();
        return view('comments.create', compact('posts'));
    }

    // (UA) Збереження нового коментаря
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'author'  => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
        ]);

        Comment::create($data);

        return redirect()
            ->route('posts.show', $data['post_id'])
            ->with('success', 'Комментарий успешно добавлен!');
    }

    // (UA) Перегляд одного коментаря
    public function show(Comment $comment): View
    {
        $comment->load('post');
        return view('comments.show', compact('comment'));
    }

    // (UA) Форма редагування
    public function edit(Comment $comment): View
    {
        $posts = Post::orderBy('title')->get();
        return view('comments.edit', compact('comment', 'posts'));
    }

    // (UA) Оновлення
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $data = $request->validate([
            'author'  => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
        ]);

        $comment->update($data);

        return redirect()
            ->route('posts.show', $data['post_id'])
            ->with('success', 'Комментарий обновлён!');
    }

    // (UA) Видалення
    public function destroy(Comment $comment): RedirectResponse
    {
        $postId = $comment->post_id;
        $comment->delete();

        return redirect()
            ->route('posts.show', $postId)
            ->with('success', 'Комментарий удалён!');
    }
}