<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index() {
        return response()->json(Category::all(), 200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'category_name' => 'required|string|max:32',
            'category_type' => 'required|string|max:16',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show($id) {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category, 200);
    }

    public function update(Request $request, $id) {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'category_name' => 'required|string|max:32',
            'category_type' => 'required|string|max:16',
        ]);

        $category->update($validated);

        return response()->json($category, 200);
    }

    public function destroy($id) {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
