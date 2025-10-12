<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\OpenApi(
 *
 *     @OA\Info(
 *         title="Blog API Documentation",
 *         description="Полная документация REST API для системы управления блогом. API поддерживает форматы JSON и XML.",
 *         version="1.0.0",
 *
 *         @OA\Contact(
 *             name="API Support",
 *             email="support@example.com"
 *         ),
 *
 *         @OA\License(
 *             name="MIT",
 *             url="https://opensource.org/licenses/MIT"
 *         )
 *     ),
 *
 *     @OA\Server(
 *         url="http://localhost:8000",
 *         description="Local Development Server"
 *     ),
 *     @OA\Server(
 *         url="http://127.0.0.1:8000",
 *         description="Alternative Local Server"
 *     ),
 *
 *     @OA\Components(
 *
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT"
 *         )
 *     )
 * )
 */
class SwaggerController extends Controller
{
    // Этот контроллер используется только для хранения основных OpenAPI аннотаций
}
