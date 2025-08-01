<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    /**
     * Показывает список всех комментариев с пагинацией.
     */
    public function index(): View
    {
        $comments = Comment::with('post')
            ->latest()
            ->paginate(20);
        
        return view('comments.index', compact('comments'));
    }

    /**
     * Показывает форму создания нового комментария.
     */
    public function create(): View
    {
        $posts = Post::orderBy('title')->get();
        return view('comments.create', compact('posts'));
    }

    /**
     * Сохраняет новый комментарий в базу данных.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'author' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
        ]);

        Comment::create([
            'author' => $request->author,
            'content' => $request->comment,
            'post_id' => $request->post_id,
        ]);

        return redirect()->route('comments.index')
            ->with('success', 'Комментарий успешно создан!');
    }

    /**
     * Показывает один конкретный комментарий.
     */
    public function show(Comment $comment): View
    {
        $comment->load('post');
        return view('comments.show', compact('comment'));
    }

    /**
     * Показывает форму редактирования комментария.
     */
    public function edit(Comment $comment): View
    {
        $posts = Post::orderBy('title')->get();
        return view('comments.edit', compact('comment', 'posts'));
    }

    /**
     * Обновляет комментарий в базе данных.
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate([
            'author' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
        ]);

        $comment->update([
            'author' => $request->author,
            'content' => $request->comment,
            'post_id' => $request->post_id,
        ]);

        return redirect()->route('comments.index')
            ->with('success', 'Комментарий успешно обновлен!');
    }

    /**
     * Удаляет комментарий из базы данных.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();
        return redirect()->route('comments.index')
            ->with('success', 'Комментарий успешно удален!');
    }
}