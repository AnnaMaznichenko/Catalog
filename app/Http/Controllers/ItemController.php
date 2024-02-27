<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Services\ExcelExporter;

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
        $itemValidation = $this->validateItem(array_merge($data, ["id_tags" => $id_tags]));
        if (!empty($itemValidation)) {
            return response()->json($itemValidation, 400);
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
        $itemValidation = $this->validateItem(array_merge($data, ["id_tags" => $id_tags]));
        if (!empty($itemValidation)) {
            return response()->json($itemValidation, 400);
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

    public function export(): void
    {
        $excelExporter = new ExcelExporter();
        $this->sendFile($excelExporter->export());
    }

    public function sendFile(Spreadsheet $spreadsheet): void
    {
        try {
            $excelWriter = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'. urlencode("items.xlsx").'"');
            $excelWriter->save('php://output');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function import(Request $request): JsonResponse
    {
        $newItems = [];
        $existingItemRow = [];
        $notValidatedItemRow = [];
        $file = $request->file("items")->getFileInfo()->getRealPath();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file);
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
                    "name" => 'required|unique:App\Models\Category,name|max:100|regex:/[\w\s\.]*/',
                ]);
                if ($validator->fails()) {
                    $notValidatedItemRow[] = $i - 1;
                    continue;
                }
                $category = Category::create(["name" => $categoryName]);
                $dataItem["id_category"] = $category->id;
            }
            $tagNames = explode(
                " ",
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
                        "name" => 'required|unique:App\Models\Tag,name|max:100|regex:/[\w\s\.]*/'
                    ]);
                    if ($validator->fails()) {
                        $notValidatedItemRow[] = $i - 1;
                        continue 2;
                    }
                    $tag = Tag::create(["name" => $tagName]);
                    $dataTags[] = $tag->id;
                }
            }
            if (!empty($this->validateItem(array_merge($dataItem, ["id_tags" => $dataTags])))) {
                $notValidatedItemRow[] = $i - 1;
                continue;
            }
            $item = Item::create($dataItem);
            $item->tags()->attach($dataTags);
            $newItems[] = array_merge(["id" => $item->id], $dataItem, ["id_tags" => $dataTags]);
        }

        return response()->json([$newItems, $existingItemRow, $notValidatedItemRow], 201);
    }

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
