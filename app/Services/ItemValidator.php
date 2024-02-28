<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class ItemValidator
{
    public function validateItem(array $toValidate): array
    {
        $validator = Validator::make($toValidate, [
            "name" => "required|unique:App\Models\Item,name|max:100|regex:/^[\w\s\.]+$/",
            "id_category" => "nullable|exists:App\Models\Category,id",
            "id_tags.*" => "exists:App\Models\Tag,id",
            "id_tags" => "array",
        ]);
        if ($validator->fails()) {
            return $validator->messages()->messages();
        }

        return [];
    }
}
