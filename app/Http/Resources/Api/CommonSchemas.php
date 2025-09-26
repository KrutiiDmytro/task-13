<?php

namespace App\Http\Resources\Api;

/**
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     title="API Response",
 *     description="Стандартный формат ответа API",
 *     @OA\Property(
 *         property="data",
 *         description="Данные ответа"
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Метаданные ответа",
 *         @OA\Property(property="total", type="integer", example=100),
 *         @OA\Property(property="count", type="integer", example=10),
 *         @OA\Property(property="per_page", type="integer", example=10),
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="total_pages", type="integer", example=10)
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Ссылки для пагинации",
 *         @OA\Property(property="first", type="string", example="http://localhost:8000/api/v1/posts?page=1"),
 *         @OA\Property(property="last", type="string", example="http://localhost:8000/api/v1/posts?page=10"),
 *         @OA\Property(property="prev", type="string", nullable=true, example=null),
 *         @OA\Property(property="next", type="string", example="http://localhost:8000/api/v1/posts?page=2")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Формат ответа при ошибке",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Сообщение об ошибке",
 *         example="Validation failed"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Детали ошибок валидации",
 *         @OA\Property(
 *             property="title",
 *             type="array",
 *             @OA\Items(type="string", example="The title field is required.")
 *         ),
 *         @OA\Property(
 *             property="email",
 *             type="array",
 *             @OA\Items(type="string", example="The email must be a valid email address.")
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     title="Success Response",
 *     description="Формат успешного ответа",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Сообщение об успехе",
 *         example="Operation completed successfully"
 *     ),
 *     @OA\Property(
 *         property="data",
 *         description="Данные ответа"
 *     )
 * )
 * 
 * @OA\Parameter(
 *     parameter="FormatParameter",
 *     name="format",
 *     in="query",
 *     description="Формат ответа",
 *     required=false,
 *     @OA\Schema(
 *         type="string",
 *         enum={"json", "xml"},
 *         default="json"
 *     )
 * )
 * 
 * @OA\Parameter(
 *     parameter="PageParameter",
 *     name="page",
 *     in="query",
 *     description="Номер страницы",
 *     required=false,
 *     @OA\Schema(
 *         type="integer",
 *         minimum=1,
 *         default=1
 *     )
 * )
 * 
 * @OA\Parameter(
 *     parameter="PerPageParameter",
 *     name="per_page",
 *     in="query",
 *     description="Количество элементов на странице",
 *     required=false,
 *     @OA\Schema(
 *         type="integer",
 *         minimum=1,
 *         maximum=100,
 *         default=10
 *     )
 * )
 * 
 * @OA\Parameter(
 *     parameter="SearchParameter",
 *     name="search",
 *     in="query",
 *     description="Поисковый запрос",
 *     required=false,
 *     @OA\Schema(type="string")
 * )
 */
class CommonSchemas
{
    // Этот класс используется только для хранения OpenAPI аннотаций
}