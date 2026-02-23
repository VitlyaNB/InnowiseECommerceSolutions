<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        // отдаем все категории
        return response()->json(Category::all());
    }

    public function show($id)
    {
        // возвращаем конктренные категории
        $category = Category::with('products')->findOrFail($id);
        return response()->json($category);
    }
}
