<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(): View
    {
        $comments = Comment::with('post')->latest()->paginate(20);

        return view('comments.index', compact('comments'));
    }

    public function create(): View
    {
        $posts = Post::orderBy('title')->get();

        return view('comments.create', compact('posts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'author' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
        ]);

        Comment::create([
            'author_name' => $data['author'],
            'content' => $data['content'],
            'post_id' => $data['post_id'],
        ]);

        return redirect()->route('posts.show', $data['post_id']);
    }

    public function show(Comment $comment): View
    {
        $comment->load('post');

        return view('comments.show', compact('comment'));
    }

    public function edit(Comment $comment): View
    {
        $posts = Post::orderBy('title')->get();

        return view('comments.edit', compact('comment', 'posts'));
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $data = $request->validate([
            'author' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
        ]);

        $comment->update([
            'author_name' => $data['author'],
            'content' => $data['content'],
            'post_id' => $data['post_id'],
        ]);

        return redirect()->route('comments.show', $comment);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()->route('comments.index');
    }
}
