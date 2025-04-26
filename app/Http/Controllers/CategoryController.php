<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        return response()->json(Category::all(), 200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|string|max:255',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show(Category $category) {
        return response()->json($category, 200);
    }

    public function update(Request $request, Category $category) {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|string|max:255',
        ]);

        $category->update($validated);

        return response()->json($category, 200);
    }

    public function destroy(Category $category) {
        $category->delete();

        return response()->json(null, 204);
    }
}
