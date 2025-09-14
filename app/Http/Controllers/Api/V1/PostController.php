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
    /**
     * @OA\Get(
     *     path="/api/v1/posts",
     *     tags={"Posts"},
     *     summary="Get all posts",
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Response format (json or xml)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"})
     *     ),
     *     @OA\Response(response=200, description="List of posts")
     * )
     */
    public function index(Request $request)
    {
        $posts = Post::with('category')->get();

    if ($request->query('format') === 'xml') {
        $postsCollection = new PostCollection($posts);
        $postsData = $postsCollection->toArray($request);
        $xml = ArrayToXml::convert(['posts' => $postsData['data']], 'root');
        return response($xml, 200)->header('Content-Type', 'application/xml');
}

        return new PostCollection($posts);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Get post by ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Response format (json or xml)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"})
     *     ),
     *     @OA\Response(response=200, description="Post details"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function show(Request $request, Post $post)
    {
        $post->load('category');
        
    if ($request->query('format') === 'xml') {
    $postArray = [
        'id' => $post->id,
        'title' => $post->title,
        'content' => $post->content,
        'category_id' => $post->category_id,
        'author_name' => $post->author_name,
        'author_email' => $post->author_email,
        'category_name' => $post->category->name ?? null,
    ];
    
        $xml = ArrayToXml::convert(['post' => $postArray], 'root');
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

        return new PostResource($post);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/posts",
     *     tags={"Posts"},
     *     summary="Create new post",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "category_id"},
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Post created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Update post",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "category_id"},
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Post updated"),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Delete post",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Post deleted"),
     *     @OA\Response(response=404, description="Post not found")
     * )
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(null, 204);
    }
}