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
    private TagService $tagService;
    private PostService $postService;

    // інʼєкція залежностей через конструктор
    public function __construct(
        CategoryService $categoryService,
        TagService $tagService,
        PostService $postService
    ) {
        $this->categoryService = $categoryService;
        $this->tagService      = $tagService;
        $this->postService     = $postService;
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
        //  Підтримуємо обидві назви параметра пошуку: q і search
        $termParam = $request->query('q', $request->query('search', ''));
        $term = (string) $termParam;

        $categoryParam = (string) $request->query('category', '');
        $tagParam      = (string) $request->query('tag', '');

        $query = Post::query()->with(['category', 'tags']);

        // Пошук по заголовку/контенту
        if ($term !== '') {
            $like = '%' . str_replace(' ', '%', $term) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('content', 'like', $like);
            });
        }

        //  Фільтр за категорією: приймаємо або id, або slug (або name як запасний варіант)
        if ($categoryParam !== '') {
            if (ctype_digit($categoryParam)) {
                $query->where('category_id', (int) $categoryParam);
            } else {
                $category = Category::where('slug', $categoryParam)
                    ->orWhere('name', $categoryParam)
                    ->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
            }
        }

        // Фільтр за тегом: приймаємо або id, або slug (або name)
        if ($tagParam !== '') {
            if (ctype_digit($tagParam)) {
                $tid = (int) $tagParam;
                $query->whereHas('tags', fn($q) => $q->where('tags.id', $tid));
            } else {
                $tag = Tag::where('slug', $tagParam)
                    ->orWhere('name', $tagParam)
                    ->first();
                if ($tag) {
                    $query->whereHas('tags', fn($q) => $q->where('tags.id', $tag->id));
                }
            }
        }

        // Якщо є колонка published_at — сортуємо з урахуванням її, інакше лише за created_at
        if (Schema::hasColumn('posts', 'published_at')) {
            $query->orderByRaw('COALESCE(published_at, created_at) DESC');
        } else {
            $query->orderByDesc('created_at');
        }

        $posts = $query->paginate(10)->withQueryString();

        // Получить категории для фильтра
        $categories = $this->categoryService->getAll();

        // Получить теги для фильтра
        $tags = $this->tagService->getAll();

        return view('posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'q'     => $term,
        ]);
    }

    /* ---------- Один пост (публічно) ---------------------------------------- */
    public function show(Post $post): View
    {
        //  Жадібне завантаження пов’язаних сутностей
        $post->load('category', 'tags', 'user', 'comments');
        return view('posts.show', compact('post'));
    }

    /* ---------- Форма створення поста (публічно) ---------------------------- */
    public function create(): View
    {
        // Використовуємо сервіси-словники
        $categories = $this->categoryService->getAll();
        $tags       = $this->tagService->getAll();

        return view('posts.create', compact('categories', 'tags'));
    }

    /* ---------- Збереження нового поста (публічно, гість теж може) ---------- */
    public function store(Request $request): RedirectResponse
    {
        //  Валідація
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'category_id'  => 'nullable|exists:categories,id',
            'tags'         => 'nullable|array',
            'tags.*'       => 'string|max:30',
            'user_id'      => 'nullable|exists:users,id',
            'author_name'  => 'nullable|string|max:255',
            'author_email' => 'nullable|email|max:255',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        //  Нормалізація тегів (ID або назви) + додатковий текстовий ввод через кому
        $tagIds = collect($data['tags'] ?? [])
            ->map(fn($tag) => is_numeric($tag) ? (int)$tag : Tag::firstOrCreate(['name' => trim($tag)])->id)
            ->all();

        $extra = collect(explode(',', (string) $request->input('tags_text', '')))
            ->map(fn($n) => trim($n))
            ->filter()
            ->map(fn($name) => Tag::firstOrCreate(['name' => $name])->id)
            ->all();

        $tagIds = array_values(array_unique(array_merge($tagIds, $extra)));

        //  Завантаження зображення (якщо є)
        $imagePath = $request->file('image')
            ? $request->file('image')->store('posts', 'public')
            : null;

        //  Автор поста: або поточний користувач, або гість з author_name/email
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

        return redirect()->route('posts.show', $post)->with('success', 'Пост успішно створено!');
    }

    /* ---------- Форма редагування (власник або адмін) ----------------------- */
    public function edit(Post $post): View
    {
        if (auth()->id() !== $post->user_id && !$this->isAdmin()) {
            abort(403, 'У вас немає прав для редагування цього поста');
        }

        $categories = $this->categoryService->getAll();
        $tags       = $this->tagService->getAll();

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    /* ---------- Оновлення поста (власник або адмін) ------------------------- */
    public function update(Request $request, Post $post): RedirectResponse
    {
        if (auth()->id() !== $post->user_id && !$this->isAdmin()) {
            abort(403, 'У вас немає прав для оновлення цього поста');
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255|unique:posts,title,' . $post->id,
            'content'     => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags'        => 'nullable|array',
            'tags.*'      => 'string|max:30',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $post->update([
            'title'       => $data['title'],
            'content'     => $data['content'],
            'category_id' => $data['category_id'] ?? null,
        ]);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = $request->file('image')->store('posts', 'public');
            $post->save();
        }

        $tagIds = collect($data['tags'] ?? [])
            ->map(fn($tag) => is_numeric($tag) ? (int)$tag : Tag::firstOrCreate(['name' => trim($tag)])->id)
            ->all();
        $post->tags()->sync($tagIds);

        return redirect()->route('posts.show', $post)->with('success', 'Пост успішно оновлено!');
    }

    /* ---------- Видалення поста (власник або адмін) ------------------------- */
    public function destroy(Post $post): RedirectResponse
    {
        if (auth()->id() !== $post->user_id && !$this->isAdmin()) {
            abort(403, 'У вас немає прав для видалення цього поста');
        }

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Пост успішно видалено!');
    }

    //  допоміжна перевірка адміністратора (підтримує is_admin і admin)
    protected function isAdmin(): bool
    {
        if (!auth()->check()) return false;
        $u = auth()->user();
        return (bool) ($u->is_admin ?? $u->admin ?? false);
    }
}
