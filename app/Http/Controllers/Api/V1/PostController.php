<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PostCollection;
use App\Http\Resources\Api\V1\PostResource;
use App\Http\Traits\FormatsResponse;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;



/**
 * @OA\Tag(
 *     name="Posts",
 *     description="Операции с постами блога"
 * )
 */
class PostController extends Controller
{
    use FormatsResponse;

    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;

        // Применяем middleware только к методам, которые изменяют данные
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts",
     *     summary="Получить список постов",
     *     tags={"Posts"},
     *
     *     @OA\Response(response=200, description="Список постов")
     * )
     */
    public function index(Request $request)
    {
        try {
            $posts = $this->postService->getFilteredPosts($request);

            return $this->formatResponse(new PostCollection($posts), $request);
        } catch (\Exception $e) {
            // Временно выводим реальную ошибку для отладки
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/posts",
     *   summary="Создать новый пост",
     *   tags={"Posts"},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"title","content","category_id"},
     *
     *       @OA\Property(property="title", type="string", maxLength=255, example="Новый пост"),
     *       @OA\Property(property="content", type="string", example="Текст..."),
     *       @OA\Property(property="category_id", type="integer", example=1),
     *       @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *       @OA\Property(property="author_name", type="string", nullable=true, example="Иван"),
     *       @OA\Property(property="author_email", type="string", format="email", nullable=true, example="ivan@example.com"),
     *       @OA\Property(property="image", type="string", nullable=true, example="images/post.jpg"),
     *       @OA\Property(property="tags", type="array", nullable=true, @OA\Items(type="string"), example={"php","laravel"}),
     *       @OA\Property(property="tags_text", type="string", nullable=true, example="php, laravel")
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="Пост создан")
     * )
     */
    private const STRING_REQUIRED_255 = 'required|string|max:255';
    private const STRING_OPTIONAL_255 = 'nullable|string|max:255';
    private const TAGS_TEXT_500 = 'nullable|string|max:500';
    private const EMAIL_STRING_255 = 'nullable|email|max:255';
    private const CONTENT_REQUIRED_STRING = 'required|string';
    private const CATEGORIES_ID_REQUIRED = 'required|exists:categories,id';
    private const USER_ID = 'nullable|exists:users,id';
    private const TAGS_ARRAY = 'nullable|array';
    private const TAGS_STRING_30 = 'string|max:30';


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' =>        self::STRING_REQUIRED_255,
                'content' =>      self::CONTENT_REQUIRED_STRING,
                'category_id' =>  self::CATEGORIES_ID_REQUIRED,
                'user_id' =>      self::USER_ID,
                'author_name' =>  self::STRING_OPTIONAL_255,
                'author_email' => self::EMAIL_STRING_255,
                'image' =>        self::STRING_OPTIONAL_255,
                'tags' =>         self::TAGS_ARRAY,
                'tags.*' =>       self::TAGS_STRING_30,
                'tags_text' =>    self::TAGS_TEXT_500,
            ]);

            $post = $this->postService->createPost($validated);

            return $this->formatResponse(
                new PostResource($post->load(['category', 'tags', 'comments'])),
                $request,
                201
            );
        } catch (ValidationException $e) {
            return $this->formatValidationErrors($e->errors(), $request);
        } catch (\Exception $e) {
            // Временно выводим реальную ошибку для отладки
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function show(Request $request, Post $post)
    {
        try {
            $post->load(['category', 'tags', 'comments']);

            return $this->formatResponse(new PostResource($post), $request);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Пост не найден', $request, 404);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/v1/posts/{post}",
     *   summary="Обновить пост",
     *   tags={"Posts"},
     *
     *   @OA\Parameter(name="post", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       type="object",
     *
     *       @OA\Property(property="title", type="string", example="Обновленный заголовок"),
     *       @OA\Property(property="content", type="string", example="Обновленный текст"),
     *       @OA\Property(property="category_id", type="integer", example=1),
     *       @OA\Property(property="user_id", type="integer", nullable=true),
     *       @OA\Property(property="author_name", type="string", nullable=true),
     *       @OA\Property(property="author_email", type="string", format="email", nullable=true),
     *       @OA\Property(property="image", type="string", nullable=true),
     *       @OA\Property(property="tags", type="array", nullable=true, @OA\Items(type="string")),
     *       @OA\Property(property="tags_text", type="string", nullable=true)
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Пост обновлен")
     * )
     */
    public function update(Request $request, Post $post)
    {
        try {
            $validated = $request->validate([
                'title' =>        self::STRING_REQUIRED_255,
                'content' =>      self::CONTENT_REQUIRED_STRING,
                'category_id' =>  self::CATEGORIES_ID_REQUIRED,
                'user_id' =>      self::USER_ID,
                'author_name' =>  self::STRING_OPTIONAL_255,
                'author_email' => self::EMAIL_STRING_255,
                'image' =>        self::STRING_OPTIONAL_255,
                'tags' =>         self::TAGS_ARRAY,
                'tags.*' =>       self::TAGS_STRING_30,
                'tags_text' =>    self::TAGS_TEXT_500,
            ]);

            $post = $this->postService->updatePost($post, $validated);

            return $this->formatResponse(
                new PostResource($post->load(['category', 'tags', 'comments'])),
                $request
            );
        } catch (ValidationException $e) {
            return $this->formatValidationErrors($e->errors(), $request);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Ошибка при обновлении поста', $request, 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{post}",
     *     summary="Удалить пост",
     *     tags={"Posts"},
     *
     *     @OA\Response(response=204, description="Пост удален")
     * )
     */
    public function destroy(Request $request, Post $post)
    {
        try {
            $post->delete();

            if ($this->getResponseFormat($request) === 'xml') {
                return response('', 204)->header('Content-Type', 'application/xml');
            }

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Ошибка при удалении поста', $request, 500);
        }
    }
}
