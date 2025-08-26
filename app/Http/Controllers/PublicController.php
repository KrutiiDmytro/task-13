<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    //  головна — показ опублікованих постів з категоріями і теґами
    public function index(Request $request)
    {
        $posts = Post::query()
            ->with(['category', 'tags'])
            ->published()
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.index', [
            'posts' => $posts,
        ]);
    }

    //  пошук за текстом з опційними фільтрами категорії/теґів
    public function search(Request $request)
    {
        $term = $request->string('q')->toString();
        $categorySlug = $request->string('category')->toString();
        $tagSlug = $request->string('tag')->toString();

        $query = Post::query()
            ->with(['category', 'tags'])
            ->published()
            ->search($term);

        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($tagSlug) {
            $tag = Tag::where('slug', $tagSlug)->first();
            if ($tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('tags.id', $tag->id);
                });
            }
        }

        $posts = $query->latest('published_at')->paginate(10)->withQueryString();

        return view('public.search', [
            'posts' => $posts,
            'q'     => $term,
        ]);
    }

    //  сторінка категорії за slug
    public function byCategory(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->with(['category', 'tags'])
            ->published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.category', [
            'category' => $category,
            'posts'    => $posts,
        ]);
    }

    // сторінка теґу за slug
    public function byTag(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->with(['category', 'tags'])
            ->published()
            ->whereHas('tags', function ($q) use ($tag) {
                $q->where('tags.id', $tag->id);
            })
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.tag', [
            'tag'   => $tag,
            'posts' => $posts,
        ]);
    }

    // перегляд одного поста за slug (якщо потрібно)
    public function show(string $slug)
    {
        $post = Post::with(['category', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.post', [
            'post' => $post,
        ]);
    }
}
