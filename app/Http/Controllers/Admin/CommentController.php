<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $comments = Comment::with(['post'])
            ->latest()
            ->paginate(15);

        return view('admin.comments.index', compact('comments'));
    }

    public function create(): View
    {
        $posts = Post::orderBy('title')->get();

        return view('admin.comments.create', compact('posts'));
    }

    public function store(CommentRequest $request): RedirectResponse
    {
        $this->commentService->create($request->validated());

        return $this->redirectToIndex('Комментарий успешно создан!');
    }

    public function show(Comment $comment): View
    {
        $comment->load('post');

        return view('admin.comments.show', compact('comment'));
    }

    public function edit(Comment $comment): View
    {
        $posts = Post::orderBy('title')->get();

        return view('admin.comments.edit', compact('comment', 'posts'));
    }

    public function update(CommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->commentService->update($comment, $request->validated());

        return $this->redirectToIndex('Комментарий успешно обновлён!');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->commentService->delete($comment);

        return $this->redirectToIndex('Комментарий успешно удалён!');
    }

    /**
     * Возврат к списку комментариев с флеш-сообщением об успехе.
     */
    private function redirectToIndex(string $message): RedirectResponse
    {
        return redirect()
            ->route('admin.comments.index')
            ->with('success', $message);
    }
}
