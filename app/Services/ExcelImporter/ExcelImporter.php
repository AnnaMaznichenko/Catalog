<?php

namespace App\Services\ExcelImporter;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use App\Services\ItemValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelImporter
{
    public function import(Spreadsheet $spreadsheet): ImportResult
    {
        $newItems = [];
        $existingItemRow = [];
        $notValidatedItemRow = [];
        $i = 2;

        while ($spreadsheet->getActiveSheet()->getCell("A" . $i)->getCalculatedValue() !== null) {
            $ItemName = $spreadsheet->getActiveSheet()->getCell("A" . $i)->getCalculatedValue();
            $existingItem = Item::where("name", $ItemName)->first();
            $i++;

            if (!empty($existingItem)) {
                $existingItemRow[] = $i - 1;
                continue;
            }

            $dataItem = [];
            $dataItem["name"] = $ItemName;
            $categoryName = $spreadsheet->getActiveSheet()->getCell("B" . $i - 1)->getCalculatedValue();
            $existingCategory = Category::where("name", $categoryName)->first();

            if (!empty($existingCategory)) {
                $dataItem["id_category"] = $existingCategory->id;
            } else {
                $validator = Validator::make(["name" => $categoryName], [
                    "name" => 'required|unique:App\Models\Category,name|max:100|regex:/^[\w\s\.]+$/',
                ]);

                if ($validator->fails()) {
                    $notValidatedItemRow[] = $i - 1;
                    continue;
                }

                $category = Category::create(["name" => $categoryName]);
                $dataItem["id_category"] = $category->id;
            }

            $tagNames = explode(
                ". ",
                $spreadsheet->getActiveSheet()->getCell("C" . $i - 1)->getCalculatedValue()
            );

            if (count($tagNames) === 0) {
                $notValidatedItemRow[] = $i - 1;
                continue;
            }

            $dataTags = [];
            foreach ($tagNames as $tagName) {
                $existingTag = Tag::where("name", $tagName)->first();

                if (!empty($existingTag)) {
                    $dataTags[] = $existingTag->id;
                } else {
                    $validator = Validator::make(["name" => $tagName], [
                        "name" => 'required|unique:App\Models\Tag,name|max:100|regex:/^[\w\s\.]+$/'
                    ]);

                    if ($validator->fails()) {
                        $notValidatedItemRow[] = $i - 1;
                        continue 2;
                    }

                    $tag = Tag::create(["name" => $tagName]);
                    $dataTags[] = $tag->id;
                }
            }

            $validator = new ItemValidator();

            if (!empty($validator->validateItem(array_merge($dataItem, $dataTags)))) {
                $notValidatedItemRow[] = $i - 1;
                continue;
            }

            $item = Item::create($dataItem);
            $item->tags()->attach($dataTags);
            $newItems[] = array_merge(["id" => $item->id], $dataItem, ["id_tags" => $dataTags]);
        }

        $importResult = new ImportResult();
        $importResult->newItems = $newItems;
        $importResult->existingItemRow = $existingItemRow;
        $importResult->notValidatedItemRow = $notValidatedItemRow;

        return $importResult;
    }
}
