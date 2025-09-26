<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CommentCollection;
use App\Http\Resources\Api\V1\CommentResource;
use App\Http\Traits\FormatsResponse;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="Операции с комментариями к постам"
 * )
 */
class CommentController extends Controller
{
    use FormatsResponse;

    public function __construct()
    {
        // Применяем middleware только к методам, которые изменяют данные
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments",
     *     summary="Получить список комментариев",
     *     description="Возвращает пагинированный список всех комментариев",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество комментариев на странице",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="post_id",
     *         in="query",
     *         description="ID поста для фильтрации комментариев",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список комментариев получен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommentResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         ),
     *         @OA\XmlContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommentResource"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = min($request->get('per_page', 15), 50);
        
        $query = Comment::with(['post']);
        
        // Фильтрация по посту - игнорируем пустые значения
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->get('post_id'));
        }
        
        $comments = $query->orderBy('created_at', 'desc')
                        ->paginate($perPage);

        $resource = new CommentCollection($comments);
        
        
        return $this->formatResponse($resource, $request);
        
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comments",
     *     summary="Создать новый комментарий",
     *     description="Создает новый комментарий к посту",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content", "post_id"},
     *             @OA\Property(property="content", type="string", example="Отличная статья!"),
     *             @OA\Property(property="author_name", type="string", example="Иван Иванов"),
     *             @OA\Property(property="author_email", type="string", format="email", example="ivan@example.com"),
     *             @OA\Property(property="post_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Комментарий создан успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/CommentResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'content' => 'required|string|max:1000',
                'author_name' => 'nullable|string|max:255',
                'author_email' => 'nullable|email|max:255',
                'post_id' => 'required|exists:posts,id'
            ]);

            $comment = Comment::create($validatedData);
            $comment->load('post');

            $resource = new CommentResource($comment);
            
            return $this->formatResponse($resource, $request, 201);
            
        } catch (ValidationException $e) {
            return $this->formatResponse([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], $request, 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments/{id}",
     *     summary="Получить комментарий по ID",
     *     description="Возвращает один комментарий по его ID",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID комментария",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Комментарий найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/CommentResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Комментарий не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment not found")
     *         )
     *     )
     * )
     */
    public function show(Request $request, Comment $comment)
    {
        $comment->load('post');
        $resource = new CommentResource($comment);
        
        return $this->formatResponse($resource, $request);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comments/{id}",
     *     summary="Обновить комментарий",
     *     description="Обновляет существующий комментарий",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID комментария",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Обновленный комментарий"),
     *             @OA\Property(property="author_name", type="string", example="Иван Петров"),
     *             @OA\Property(property="author_email", type="string", format="email", example="ivan.petrov@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Комментарий обновлен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/CommentResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Комментарий не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function update(Request $request, Comment $comment)
    {
        try {
            $validatedData = $request->validate([
                'content' => 'sometimes|required|string|max:1000',
                'author_name' => 'sometimes|nullable|string|max:255',
                'author_email' => 'sometimes|nullable|email|max:255'
            ]);

            $comment->update($validatedData);
            $comment->load('post');

            $resource = new CommentResource($comment);
            
            return $this->formatResponse($resource, $request);
            
        } catch (ValidationException $e) {
            return $this->formatResponse([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], $request, 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comments/{id}",
     *     summary="Удалить комментарий",
     *     description="Удаляет комментарий по ID",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID комментария",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Комментарий удален успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Комментарий не найден"
     *     )
     * )
     */
    public function destroy(Request $request, Comment $comment)
    {
        $comment->delete();
    
        if ($this->getResponseFormat($request) === 'xml') {
        return response('', 204)->header('Content-Type', 'application/xml');
        }
    
        return response()->json(null, 204);
    }
}