<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TagController extends Controller
{
    public function index()
    {
        return Tag::paginate(20);
    }

    public function show(Tag $tag): Tag
    {
        return $tag;
    }

    public function store(Request $request): JsonResponse
    {
        $data = [
            "name" => trim($request->get("name"))
        ];
        $validator = Validator::make($data, [
            "name" => 'required|unique:App\Models\Tag,name|max:100'
        ]);
        if ($validator->fails()){
            return response()->json($validator->messages()->messages(), 400);
        }
        $tag = Tag::create($data);

        return response()->json($tag, 201);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $data = [
            "name" => trim($request->get("name"))
        ];
        $validator = Validator::make($data, [
            "name" => 'required|unique:App\Models\Tag,name|max:100'
        ]);
        if ($validator->fails()){
            return response()->json($validator->messages()->messages(), 400);
        }
        $tag->update($data);

        return response()->json($tag, 202);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json([], 204);
    }
}
