<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index()
    {
        return Item::with(["category", "tags"])->paginate(10);
    }

    public function show(Item $item): Item
    {
        return $item;
    }

    public function store(Request $request): JsonResponse
    {
        $data = [
            "name" => trim($request->get("name")),
            "id_category" => $request->get("id_category"),
        ];
        $id_tags = $request->get("id_tags") ?? [];
        $validator = Validator::make(array_merge($data, ["id_tags" => $id_tags]), [
            "name" => "required|unique:App\Models\Item,name|max:100",
            "id_category" => "nullable|exists:App\Models\Category,id",
            "id_tags.*" => "exists:App\Models\Tag,id",
            "id_tags" => "array",
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->messages(), 400);
        }
        $item = Item::create($data);
        $item->tags()->attach($id_tags);

        return response()->json(array_merge(["id" => $item->id], $data, ["id_tags" => $id_tags]), 201);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        $data = [
            "id" => $item->id,
            "name" => trim($request->get("name")),
            "id_category" => $request->get("id_category"),
        ];
        $id_tags = $request->get("id_tags") ?? [];
        $validator = Validator::make(array_merge($data, ["id_tags" => $id_tags]), [
            "name" => "required|unique:App\Models\Item,name|max:100",
            "id_category" => "nullable|exists:App\Models\Category,id",
            "id_tags.*" => "exists:App\Models\Tag,id",
            "id_tags" => "array",
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->messages(), 400);
        }
        $item->update($data);
        $item->tags()->sync($id_tags);

        return response()->json(array_merge($data, ["id_tags" => $id_tags]), 202);
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json([], 204);
    }
}
