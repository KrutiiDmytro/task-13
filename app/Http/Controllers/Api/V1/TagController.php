<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TagCollection;
use App\Http\Resources\Api\V1\TagResource;
use App\Http\Traits\FormatsResponse;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Tags",
 *     description="Операции с тегами постов"
 * )
 */
class TagController extends Controller
{
    use FormatsResponse;

    public function __construct()
    {
        // Применяем middleware только к методам, которые изменяют данные
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tags",
     *     summary="Получить список тегов",
     *     description="Возвращает пагинированный список всех тегов с количеством постов",
     *     tags={"Tags"},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество тегов на странице",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Поиск по названию тега",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="php")
     *     ),
     *
     *     @OA\Parameter(
     *         name="include_posts",
     *         in="query",
     *         description="Включить посты в ответ",
     *         required=false,
     *
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Список тегов получен успешно",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TagResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         ),
     *
     *         @OA\XmlContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TagResource"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = min($request->get('per_page', 20), 50);

        $query = Tag::query();

        // Поиск по названию
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        // Подсчет постов для каждого тега
        $query->withCount('posts');

        // Включение постов если запрошено
        if ($request->boolean('include_posts')) {
            $query->with(['posts' => function ($query) {
                $query->latest()->limit(5); // Ограничиваем количество постов
            }]);
        }

        $tags = $query->orderBy('name')
            ->paginate($perPage);

        $resource = new TagCollection($tags);

        return $this->formatResponse($resource, $request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tags",
     *     summary="Создать новый тег",
     *     description="Создает новый тег",
     *     tags={"Tags"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string", example="Laravel"),
     *             @OA\Property(property="slug", type="string", example="laravel")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Тег создан успешно",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", ref="#/components/schemas/TagResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *
     *         @OA\JsonContent(
     *
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
                'name' => 'required|string|max:255|unique:tags,name',
                'slug' => 'nullable|string|max:255|unique:tags,slug',
            ]);

            // Автоматическое создание slug если не указан
            if (empty($validatedData['slug'])) {
                $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);
            }

            $tag = Tag::create($validatedData);

            // Принудительно сохраняем в базу
            $tag->save();
            $tag->refresh();

            $tag->loadCount('posts');

            $resource = new TagResource($tag);

            return $this->formatResponse($resource, $request, 201);
        } catch (ValidationException $e) {
            return $this->formatResponse([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], $request, 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tags/{id}",
     *     summary="Получить тег по ID",
     *     description="Возвращает один тег по его ID с опциональными постами",
     *     tags={"Tags"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID тега",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="include_posts",
     *         in="query",
     *         description="Включить посты в ответ",
     *         required=false,
     *
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Тег найден",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", ref="#/components/schemas/TagResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Tag not found")
     *         )
     *     )
     * )
     */
    public function show(Request $request, Tag $tag)
    {
        $tag->loadCount('posts');

        // Включение постов если запрошено
        if ($request->boolean('include_posts')) {
            $tag->load(['posts' => function ($query) {
                $query->with(['category', 'tags'])->latest();
            }]);
        }

        $resource = new TagResource($tag);

        return $this->formatResponse($resource, $request);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/tags/{id}",
     *     summary="Обновить тег",
     *     description="Обновляет существующий тег",
     *     tags={"Tags"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID тега",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="Laravel Framework"),
     *             @OA\Property(property="slug", type="string", example="laravel-framework")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Тег обновлен успешно",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", ref="#/components/schemas/TagResource")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function update(Request $request, Tag $tag)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:tags,name,' . $tag->id,
                'slug' => 'sometimes|nullable|string|max:255|unique:tags,slug,' . $tag->id,
            ]);

            // Автоматическое обновление slug если изменилось имя, но slug не указан
            if (isset($validatedData['name']) && ! isset($validatedData['slug'])) {
                $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);
            }

            $tag->update($validatedData);
            $tag->loadCount('posts');

            $resource = new TagResource($tag);

            return $this->formatResponse($resource, $request);
        } catch (ValidationException $e) {
            return $this->formatResponse([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], $request, 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tags/{id}",
     *     summary="Удалить тег",
     *     description="Удаляет тег по ID",
     *     tags={"Tags"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID тега",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Тег удален успешно",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Tag deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден"
     *     )
     * )
     */
    public function destroy(Request $request, Tag $tag)
    {
        $tag->posts()->detach();
        $tag->delete();

        if ($this->getResponseFormat($request) === 'xml') {
            return response('', 204)->header('Content-Type', 'application/xml');
        }

        return response()->json(null, 204);
    }
}
