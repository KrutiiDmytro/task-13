<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\CategoryService;
use App\Services\PostService;
use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;


class PostController extends Controller
{
    // сервісні залежності контролера
    private CategoryService $categoryService;
    private TagService    $tagService;
    private PostService   $postService;

    // інʼєкція залежностей через конструктор
    public function __construct(
        CategoryService $categoryService,
        TagService $tagService,
        PostService $postService
    ) {
        $this->categoryService = $categoryService;
        $this->tagService      = $tagService;
        $this->postService     = $postService;
        
        // Применяем middleware auth только к методам, которые изменяют данные
        $this->middleware('auth')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     *  Головна/список постів для публічної частини.
     * Підтримує параметри:
     *  - q або search: текстовий пошук (title/content)
     *  - category: id або slug категорії
     *  - tag: id або slug теґу
     */
    public function index(Request $request): View
    {
        $posts = $this->postService->getFilteredPosts($request);
        $categories = $this->categoryService->getAll();
        $tags = $this->tagService->getAll();

        // Підтримуємо обидві назви параметра пошуку: q і search
        $termParam = $request->query('q', $request->query('search', ''));
        $term = (string) $termParam;
        
        $categoryId = $request->query('category');
        $tagId = $request->query('tag');

        return view('posts.index', compact('posts', 'categories', 'tags', 'term', 'categoryId', 'tagId'));
    }

    /**
     * Показати один пост.
     */
    public function show(Post $post): View
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Показати форму створення нового поста.
     */
    public function create(): View
    {
        $categories = $this->categoryService->getAll();
        $tags = $this->tagService->getAll();
        $users = User::all(); // Для выбора автора
        return view('posts.create', compact('categories', 'tags', 'users'));
    }

    /**
     * Зберегти новий пост у базу даних.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'category_id'  => 'required|exists:categories,id',
            'user_id'      => 'nullable|exists:users,id',
            'author_name'  => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tags'         => 'nullable|array',
            'tags.*'       => 'string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('images/posts', 'public');
        }

        // Устанавливаем user_id текущего пользователя, если не указан
        if (!isset($validatedData['user_id'])) {
            $validatedData['user_id'] = auth()->id();
        }

        $post = $this->postService->createPost($validatedData);

        return redirect()->route('posts.index')->with('success', 'Пост успешно создан!');
    }

    /**
     * Показати форму редагування поста.
     */
    public function edit(Post $post): View
    {
        // Простая проверка прав доступа
        if (!$this->canManagePost($post)) {
            abort(403);
        }

        $categories = $this->categoryService->getAll();
        $tags = $this->tagService->getAll();
        $users = User::all();
        return view('posts.edit', compact('post', 'categories', 'tags', 'users'));
    }

    /**
     * Оновити пост у базі даних.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        // Простая проверка прав доступа
        if (!$this->canManagePost($post)) {
            abort(403);
        }

        $validatedData = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'category_id'  => 'required|exists:categories,id',
            'user_id'      => 'nullable|exists:users,id',
            'author_name'  => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tags'         => 'nullable|array',
            'tags.*'       => 'string|max:255',
        ]);

        if ($request->hasFile('image')) {
            // Удаляем старое изображение, если оно есть
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $validatedData['image'] = $request->file('image')->store('images/posts', 'public');
        }

        $this->postService->updatePost($post, $validatedData);

        return redirect()->route('posts.index')->with('success', 'Пост успешно обновлен!');
    }

    /**
     * Видалити пост з бази даних.
     */
    public function destroy(Post $post): RedirectResponse
    {
        // Простая проверка прав доступа
        if (!$this->canManagePost($post)) {
            abort(403);
        }

        // Удаляем изображение, если оно есть
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $this->postService->deletePost($post);

        return redirect()->route('posts.index')->with('success', 'Пост успешно удален!');
    }

    /**
     * Проверяет, может ли пользователь управлять постом.
     */
    private function canManagePost(Post $post): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Админы могут управлять любыми постами
        if ($user->admin) {
            return true;
        }

        // Пользователи могут управлять только своими постами
        return $post->user_id === $user->id;
    }

    /**
     * Проверяет, является ли текущий пользователь администратором.
     */
    private function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->admin;
    }
}