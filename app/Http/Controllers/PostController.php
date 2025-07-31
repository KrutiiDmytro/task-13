<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\CategoryService;
use App\Services\PostService;
use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    protected PostService $postService;
    protected CategoryService $categoryService;
    protected TagService $tagService;

    /**
     * Внедряем наши сервисы через конструктор.
     * Laravel автоматически создаст их экземпляры.
     */
    public function __construct(
        PostService $postService,
        CategoryService $categoryService,
        TagService $tagService
    ) {
        $this->postService = $postService;
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
    }

    /**
     * Показывает список постов с фильтрами и пагинацией.
     */
    public function index(Request $request): View
    {
        $posts = $this->postService->getFilteredPosts($request);
        $categories = $this->categoryService->getAll();
        $tags = $this->tagService->getAll();

        return view('posts.index', compact('posts', 'categories', 'tags'));
    }

    /**
     * Показывает один конкретный пост.
     * Используется Route Model Binding: Laravel автоматически найдет пост по ID.
     */
    public function show(Post $post): View
    {
        // Подгружаем связанные комментарии
        $post->load('comments');

        return view('posts.show', compact('post'));
    }

    /**
     * Показывает форму для создания нового поста.
     */
    public function create(): View
    {
        $categories = $this->categoryService->getAll();
        $tags = $this->tagService->getAll();

        return view('posts.create', compact('categories', 'tags'));
    }

    /**
     * Сохраняет новый пост в базу данных.
     */
    public function store(Request $request): RedirectResponse
    {
        // Валидация входных данных
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id', // Проверяем, что каждый ID тега существует
        ]);

        // Используем сервис для создания поста
        $post = $this->postService->createPost($validatedData);

        // Перенаправляем на страницу свежесозданного поста с сообщением об успехе
        return redirect()->route('posts.show', $post)
            ->with('success', 'Пост успешно создан!');
    }
}