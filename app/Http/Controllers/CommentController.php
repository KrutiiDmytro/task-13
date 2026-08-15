<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

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

    public function store(CommentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->commentService->create($data);

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

    public function update(CommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->commentService->update($comment, $request->validated());

        return redirect()->route('comments.show', $comment);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->commentService->delete($comment);

        return redirect()->route('comments.index');
    }
}
