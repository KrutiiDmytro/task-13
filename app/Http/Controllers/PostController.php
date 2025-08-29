<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Post;
use App\Models\Tag;
use App\Services\CategoryService;
use App\Services\PostService;
use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    protected PostService     $postService;
    protected CategoryService $categoryService;
    protected TagService      $tagService;

    public function __construct(
        PostService     $postService,
        CategoryService $categoryService,
        TagService      $tagService
    ) {
        $this->postService     = $postService;
        $this->categoryService = $categoryService;
        $this->tagService      = $tagService;
    }

    private function isAdmin(): bool
    {
        $u = auth()->user();
        return $u && ( ($u->is_admin ?? false) || (method_exists($u, 'hasRole') && $u->hasRole('admin')) );
    }

    /* ---------- Список постов ------------------------------------------------ */
    public function index(Request $request): View
    {
        $posts      = $this->postService->getFilteredPosts($request);
        $categories = $this->categoryService->getAll();
        $tags       = $this->tagService->getAll();

        return view('posts.index', compact('posts', 'categories', 'tags'));
    }

    /* ---------- Один пост ---------------------------------------------------- */
    public function show(Post $post): View
    {
        $post->load('category', 'tags', 'user', 'comments');
        return view('posts.show', compact('post'));
    }

    /* ---------- Форма создания ---------------------------------------------- */
    public function create(): View
    {
        $categories = $this->categoryService->getAll();
        $tags       = $this->tagService->getAll();

        return view('posts.create', compact('categories', 'tags'));
    }

    /* ---------- Сохраняем новый пост ---------------------------------------- */
    public function store(Request $request): RedirectResponse
    {
    $data = $request->validate([
        'title'        => 'required|string|max:255',
        'content'      => 'required|string',
        'category_id'  => 'nullable|exists:categories,id',
        'tags'         => 'nullable|array',
        'tags.*'       => 'string|max:30',
        'user_id'      => 'nullable|exists:users,id',
        'author_name'  => 'nullable|string|max:255',
        'author_email' => 'nullable|email|max:255',
        'image' => [
                          'nullable',
                          'image',
                          'mimes:jpg,jpeg,png,webp,gif',
                          'max:4096',
                          
        ]
    ]);

    $tagIds = collect($data['tags'] ?? [])
        ->map(fn ($tag) => is_numeric($tag) ? (int)$tag : Tag::firstOrCreate(['name' => trim($tag)])->id)
        ->all();

    $extra = collect(explode(',', (string) $request->input('tags_text', '')))
        ->map(fn($n) => trim($n))
        ->filter()
        ->map(fn($name) => Tag::firstOrCreate(['name' => $name])->id)
        ->all();

    $tagIds = array_values(array_unique(array_merge($tagIds, $extra)));

    $imagePath = $request->file('image')
        ? $request->file('image')->store('posts', 'public')
        : null;

    $userId      = $data['user_id'] ?? auth()->id();
    $authorName  = $userId ? (auth()->user()->name  ?? null) : ($data['author_name']  ?? null);
    $authorEmail = $userId ? (auth()->user()->email ?? null) : ($data['author_email'] ?? null);

    $post = Post::create([
        'title'        => $data['title'],
        'content'      => $data['content'],
        'category_id'  => $data['category_id'] ?? null,
        'user_id'      => $userId,
        'author_name'  => $authorName,
        'author_email' => $authorEmail,
        'image'        => $imagePath,
        'date'         => now()->toDateString(),
    ]);

    $post->tags()->sync($tagIds);

    return redirect()->route('posts.show', $post)->with('success', 'Пост успешно создан!');
    }

    /* ---------- Форма редактирования --------------------------------------- */
    public function edit(Post $post): View
    {
        if (auth()->id() !== $post->user_id && !$this->isAdmin()) {
            abort(403, 'У вас нет прав для редактирования этого поста');
        }

        $categories = $this->categoryService->getAll();
        $tags       = $this->tagService->getAll();

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }
    /* ---------- Обновляем существующий пост -------------------------------- */
    public function update(Request $request, Post $post): RedirectResponse
    {
    // права
    if (auth()->id() !== $post->user_id && !$this->isAdmin()) {
        abort(403, 'У вас нет прав для редактирования этого поста');
    }

    $data = $request->validate([
        'title'       => 'required|string|max:255|unique:posts,title,' . $post->id,
        'content'     => 'required|string',
        'category_id' => 'nullable|exists:categories,id',
        'tags'        => 'nullable|array',
        'tags.*'      => 'string|max:30',
        // при необходимости добавьте dimensions: ...
        'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
    ]);

    $post->update([
        'title'       => $data['title'],
        'content'     => $data['content'],
        'category_id' => $data['category_id'] ?? null,
    ]);

    // замена изображения
    if ($request->hasFile('image')) {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->image = $request->file('image')->store('posts', 'public');
        $post->save();
    }

    // теги
    $tagIds = collect($data['tags'] ?? [])
        ->map(fn($tag) => is_numeric($tag) ? (int)$tag : Tag::firstOrCreate(['name' => trim($tag)])->id)
        ->all();
    $post->tags()->sync($tagIds);

    return redirect()->route('posts.show', $post)->with('success', 'Пост успешно обновлён!');
    }

    /* ---------- Удаляем пост ------------------------------------------------ */
    public function destroy(Post $post): RedirectResponse
    {
        // Проверяем права: либо владелец поста, либо админ
                if (auth()->id() !== $post->user_id && !$this->isAdmin()) {
            abort(403, 'У вас нет прав для удаления этого поста');
        }

        $post->delete();
        return redirect()->route('posts.index')
                         ->with('success', 'Пост успешно удалён!');
    }

}