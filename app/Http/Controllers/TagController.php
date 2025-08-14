<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TagController extends Controller
{
    /**
     * Показывает список всех тегов.
     */
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $tags = Tag::query()
            //  Якщо є запит, фільтруємо по 'name'
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where('name', 'like', '%' . $q . '%');
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('tags.index', compact('tags', 'q'));
    }

    /**
     * Показывает форму создания нового тега.
     */
    public function create(): View
    {
        return view('tags.create');
    }

    /**
     * Сохраняет новый тег в базу данных.
     */
    public function store(Request $request)
    {
        // (UA) Валідація: унікальне ім’я тегу
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        $tag = Tag::create($validated); // (UA) slug згенерується автоматично в моделі

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ]
        ]);
    }

/**
     * Показати пости за конкретним тегом (slug).
     *  Видаємо список постів, прикріплених до цього тегу, з пагінацією.
     */
        public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        // (UA) Витягуємо пости цього тегу; можна додати with('user','category','tags') за потреби
        $posts = $tag->posts()
            ->with('tags') // (UA) щоб одразу мати теги кожного поста
            ->latest('id')
            ->paginate(10);

        return view('tags.show', compact('tag', 'posts'));
    }



    /**
     * Показывает форму редактирования тега.
     */
    public function edit(Tag $tag): View
    {
        return view('tags.edit', compact('tag'));
    }

    /**
     * Обновляет тег в базе данных.
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);

        $tag->update([
            'name' => $request->name,
        ]);

        return redirect()->route('tags.index')
            ->with('success', 'Тег успешно обновлен!');
    }

    /**
     * Удаляет тег из базы данных.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();
        return redirect()->route('tags.index')
            ->with('success', 'Тег успешно удален!');
    }

        /**
     * Сохраняет новый тег через AJAX запрос.
     */
    public function storeAjax(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
            ]
        ]);
    }
}