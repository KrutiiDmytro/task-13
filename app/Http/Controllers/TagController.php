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
    public function index(): View
    {
        $tags = Tag::withCount('posts')->orderBy('name')->get();
        return view('tags.index', compact('tags'));
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags',
        ]);

        Tag::create([
            'name' => $request->name,
        ]);

        return redirect()->route('tags.index')
            ->with('success', 'Тег успешно создан!');
    }

    /**
     * Показывает один конкретный тег.
     */
    public function show(Tag $tag): View
    {
        $tag->load('posts');
        return view('tags.show', compact('tag'));
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