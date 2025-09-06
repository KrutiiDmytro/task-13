<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;

class CategoryController extends Controller
{
public function index(Request $request)
    {
    $categories = Category::all();

    if ($request->query('format') === 'xml') {
        $xml = ArrayToXml::convert(['categories' => $categories->toArray()]);
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    return CategoryResource::collection($categories)
        ->response()
        ->setEncodingOptions(JSON_PRETTY_PRINT);
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories',
        'description' => 'nullable|string'
    ]);
    
    $category = Category::create($validated);
    return new CategoryResource($category);
    }

    public function show(Request $request, Category $category)
    {
    if ($request->query('format') === 'xml') {
        $categoryArray = (new CategoryResource($category))->toArray($request);
        $xml = ArrayToXml::convert(['category' => $category->toArray()]);
        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
    
    return new CategoryResource($category);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'sometimes|nullable|string'
        ]);

        $category->update($validated);
        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        // Удалить категорию
        $category->delete();
        return response()->json(null, 204);
    }
}
