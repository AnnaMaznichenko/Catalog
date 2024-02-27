<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::paginate(10);
    }

    public function show(Category $category): Category
    {
        return $category;
    }

    public function store(Request $request): JsonResponse
    {
        $data = [
            "name" => trim($request->get("name")),
            "id_category" => $request->get("id_category"),
        ];
        $validator = Validator::make($data, [
            "name" => 'required|unique:App\Models\Category,name|max:100|regex:/^[\w\s\.]+$/',
            "id_category" => 'nullable|exists:App\Models\Category,id',
        ]);
        if ($validator->fails()){
            return response()->json($validator->messages()->messages(), 400);
        }
        $category = Category::create($data);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = [
            "id" => $category->id,
            "name" => trim($request->get("name")),
            "id_category" => $request->get("id_category"),
        ];
        $validator = Validator::make($data, [
            "name" => 'required|unique:App\Models\Category,name|max:100|regex:/^[\w\s\.]+$/',
            "id_category" => 'nullable|exists:App\Models\Category,id|different:id',
        ]);
        if ($validator->fails()){
            return response()->json($validator->messages()->messages(), 400);
        }
        $category->update($data);

        return response()->json($category, 202);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([], 204);
    }
}
