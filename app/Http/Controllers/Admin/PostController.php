<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    public function index(): \Illuminate\View\View
    {
    $posts = Post::with(['category', 'tags', 'user'])
        ->latest()
        ->paginate(15);

    $categories = Category::all();
    $tags = Tag::all();

    return view('admin.posts.index', compact('posts', 'categories', 'tags'));
    }

    public function create(): View
{
    $categories = Category::all();
    $tags = Tag::all();
    $users = \App\Models\User::all();
    
    return view('admin.posts.create', compact('categories', 'tags', 'users'));
}
    public function store(Request $request): RedirectResponse
    {
    $data = $request->validate([
        'title'        => 'required|string|max:255',
        'content'      => 'required|string',
        'category_id'  => 'nullable|exists:categories,id',
        'tags'         => 'nullable|array',
        'tags.*'       => 'string|max:30',
        'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
    ]);

    $tagIds = collect($data['tags'] ?? [])
        ->map(fn($tag) => is_numeric($tag) ? (int)$tag : Tag::firstOrCreate(['name' => trim($tag)])->id)
        ->all();

    $imagePath = $request->file('image')
        ? $request->file('image')->store('posts', 'public')
        : null;

    $post = Post::create([
        'title'       => $data['title'],
        'content'     => $data['content'],
        'category_id' => $data['category_id'] ?? null,
        'user_id'     => auth()->id(),
        'image'       => $imagePath,
        'date'        => now()->toDateString(),
    ]);

    $post->tags()->sync($tagIds);

    return redirect()->route('admin.posts.index')->with('success', 'Пост успешно создан!');
    }

    public function show(Post $post): View
    {
        $post->load(['category', 'tags', 'user', 'comments']);
        return view('admin.posts.show', compact('post'));
    }

    public function edit(Post $post): View
    {
        $categories = Category::all();
        $tags = Tag::all();
        
        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
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

    return redirect()->route('admin.posts.index')->with('success', 'Пост успешно обновлён!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Пост успешно удалён!');
    }
}