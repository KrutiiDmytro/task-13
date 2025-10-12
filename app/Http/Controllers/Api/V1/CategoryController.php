<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryCollection;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Traits\FormatsResponse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Операции с категориями"
 * )
 */
class CategoryController extends Controller
{
    use FormatsResponse;

    public function __construct()
    {
        // Применяем middleware только к методам, которые изменяют данные
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Получить список категорий",
     *     description="Возвращает список всех категорий с количеством постов",
     *     tags={"Categories"},
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
     *         description="Количество категорий на странице",
     *         required=false,
     *
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Поиск по названию категории",
     *         required=false,
     *
     *         @OA\Schema(type="string", example="Технологии")
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа (json или xml)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $model = app(\App\Models\Category::class);
            $query = $model->withCount('posts');

            // Поиск по названию
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%{$search}%");
            }

            $categories = $query->orderBy('name')
                ->paginate(min((int) $request->get('per_page', 10), 50));

            // Для XML упрощаем структуру, чтобы избежать ошибок сериализации
            if ($this->getResponseFormat($request) === 'xml') {
                $categoriesData = [];
                foreach ($categories->getCollection() as $category) {
                    $categoriesData[] = (new \App\Http\Resources\Api\V1\CategoryResource($category))->toArray($request);
                }

                $data = [
                    'data' => $categoriesData,
                    'meta' => [
                        'total' => (int) $categories->total(),
                        'count' => (int) $categories->count(),
                        'per_page' => (int) $categories->perPage(),
                        'current_page' => (int) $categories->currentPage(),
                        'total_pages' => (int) $categories->lastPage(),
                    ],
                ];

                return $this->formatResponse($data, $request);
            }

            return $this->formatResponse(new CategoryCollection($categories), $request);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Ошибка при получении списка категорий', $request, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     summary="Создать новую категорию",
     *     description="Создает новую категорию",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа (json или xml)",
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
     *             @OA\Property(property="name", type="string", example="Технологии"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Описание категории"),
     *             @OA\Property(property="slug", type="string", nullable=true, example="technologies")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Категория успешно создана",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:500',
                'slug' => 'nullable|string|max:255|unique:categories,slug',
            ]);

            // Автогенерация slug если не указан
            if (empty($validated['slug'])) {
                $validated['slug'] = \Str::slug($validated['name']);
            }

            $category = app(\App\Models\Category::class)->create($validated);

            return $this->formatResponse(
                new CategoryResource($category->loadCount('posts')),
                $request,
                201
            );
        } catch (ValidationException $e) {
            return $this->formatValidationErrors($e->errors(), $request);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Ошибка при создании категории', $request, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{category}",
     *     summary="Получить категорию по ID",
     *     description="Возвращает конкретную категорию с количеством постов",
     *     tags={"Categories"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="ID категории",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="include_posts",
     *         in="query",
     *         description="Включить список постов категории",
     *         required=false,
     *
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа (json или xml)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Категория не найдена"
     *     )
     * )
     */
    public function show(Request $request, Category $category)
    {
        try {
            $category->loadCount('posts');

            // Опционально загружаем посты категории
            if ($request->boolean('include_posts')) {
                $category->load(['posts' => function ($query) {
                    $query->with(['tags'])->latest('created_at');
                }]);
            }

            return $this->formatResponse(new CategoryResource($category), $request);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Категория не найдена', $request, 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/categories/{category}",
     *     summary="Обновить категорию",
     *     description="Обновляет существующую категорию",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="ID категории",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа (json или xml)",
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
     *             @OA\Property(property="name", type="string", example="Обновленное название"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Обновленное описание"),
     *             @OA\Property(property="slug", type="string", nullable=true, example="updated-slug")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Категория успешно обновлена",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Категория не найдена"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function update(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:500',
                'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            ]);

            // Автогенерация slug если не указан
            if (empty($validated['slug']) && isset($validated['name'])) {
                $validated['slug'] = \Str::slug($validated['name']);
            }

            $category->update($validated);

            return $this->formatResponse(
                new CategoryResource($category->loadCount('posts')),
                $request
            );
        } catch (ValidationException $e) {
            return $this->formatValidationErrors($e->errors(), $request);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Ошибка при обновлении категории', $request, 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{category}",
     *     summary="Удалить категорию",
     *     description="Удаляет категорию по ID (только если нет связанных постов)",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="ID категории",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="Формат ответа (json или xml)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"json", "xml"}, example="json")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Категория успешно удалена"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категория не найдена"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Нельзя удалить категорию с постами"
     *     )
     * )
     */
    public function destroy(Request $request, Category $category)
    {
        try {
            $service = app(\App\Services\CategoryService::class);
            $categoryToDelete = $service->find($category->id) ?? $category;

            if ($categoryToDelete->posts()->count() > 0) {
                return $this->formatErrorResponse(
                    'Нельзя удалить категорию с постами',
                    $request,
                    409
                );
            }

            $service->delete($categoryToDelete);

            if ($this->getResponseFormat($request) === 'xml') {
                return response('', 204)->header('Content-Type', 'application/xml');
            }

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return $this->formatErrorResponse('Ошибка при удалении категории', $request, 500);
        }
    }
}
