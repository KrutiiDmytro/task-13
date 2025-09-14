<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use App\Http\Resources\Api\V1\CommentCollection;

class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/comments",
     *     tags={"Comments"},
     *     summary="Get all comments",
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Response format (json or xml)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"})
     *     ),
     *     @OA\Response(response=200, description="List of comments")
     * )
     */
    public function index(Request $request)
    {
        $comments = Comment::with('post')->get();

        if ($request->query('format') === 'xml') {
            $commentsArray = CommentResource::collection($comments)->toArray($request);
            $xml = ArrayToXml::convert(['comments' => $commentsArray], 'root');            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

            return new CommentCollection($comments);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comments",
     *     tags={"Comments"},
     *     summary="Create new comment",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content", "author", "post_id"},
     *             @OA\Property(property="content", type="string", description="Comment content"),
     *             @OA\Property(property="author", type="string", description="Comment author"),
     *             @OA\Property(property="post_id", type="integer", description="Post ID")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'author' => 'required|string|max:255',
            'post_id' => 'required|exists:posts,id'
        ]);

        $comment = Comment::create($validated);
        $comment->load('post');

        return new CommentResource($comment);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments/{id}",
     *     tags={"Comments"},
     *     summary="Get comment by ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Response format (json or xml)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"})
     *     ),
     *     @OA\Response(response=200, description="Comment details"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function show(Request $request, Comment $comment)
    {
        $comment->load('post');

        if ($request->query('format') === 'xml') {
            $commentArray = (new CommentResource($comment))->toArray($request);
            $xml = ArrayToXml::convert(['comment' => $commentArray]);
            return response($xml, 200)->header('Content-Type', 'application/xml');
        }

        return new CommentResource($comment);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comments/{id}",
     *     tags={"Comments"},
     *     summary="Update comment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", description="Comment content"),
     *             @OA\Property(property="author", type="string", description="Comment author")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Comment updated"),
     *     @OA\Response(response=404, description="Comment not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'content' => 'sometimes|required|string',
            'author' => 'sometimes|required|string|max:255'
        ]);

        $comment->update($validated);
        $comment->load('post');

        return new CommentResource($comment);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comments/{id}",
     *     tags={"Comments"},
     *     summary="Delete comment",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Comment deleted"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(null, 204);
    }
}