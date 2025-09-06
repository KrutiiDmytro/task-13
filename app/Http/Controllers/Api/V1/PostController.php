<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PostResource;
use App\Http\Resources\Api\V1\PostCollection;
use App\Models\Post;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;

class PostController extends Controller
{

    public function index(Request $request)
    {
        $posts = Post::with('category')->get();

        if ($request->query('format') === 'xml') {
            $postsArray = PostResource::collection($posts)->toArray($request);
            $xml = ArrayToXml::convert($postsArray, 'posts');
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        return new PostCollection($posts);
    }

    public function show(Request $request, Post $post)
    {
        $post->load('category');
        
        if ($request->query('format') === 'xml') {
            $postArray = (new PostResource($post))->toArray($request);
            $xml = ArrayToXml::convert($postArray, 'post');
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        return new PostResource($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'category_id' => 'required|exists:categories,id'
    ]);

        $post = Post::create($validated);
        $post->load('category');
        
        return new PostResource($post);
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'category_id' => 'required|exists:categories,id'
    ]);

        $post->update($validated);
        $post->load('category');
        
        return new PostResource($post);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(null, 204);
    }

}