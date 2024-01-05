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
        $validator = Validator::make($data, [
            "name" => "required|unique:App\Models\Item,name|max:100",
            "id_category" => "nullable|exists:App\Models\Category,id",
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->messages(), 400);
        }
        $item = Item::create($data);

        return response()->json($item, 201);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        $data = [
            "id" => $item->id,
            "name" => trim($request->get("name")),
            "id_category" => $request->get("id_category"),
        ];
        $validator = Validator::make($data, [
            "name" => "required|unique:App\Models\Item,name|max:100",
            "id_category" => "nullable|exists:App\Models\Category,id",
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->messages(), 400);
        }
        $item->update($data);

        return response()->json($item, 202);
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json([], 204);
    }
}
