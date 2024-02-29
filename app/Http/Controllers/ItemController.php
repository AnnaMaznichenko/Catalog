<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ExcelExporter;
use App\Services\ExcelImporter\ExcelImporter;
use App\Services\ItemValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $validator = new ItemValidator();
        $itemValidation = $validator->validateItem(array_merge($data, ["id_tags" => $id_tags]));
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
        $validator = new ItemValidator();
        $itemValidation = $validator->validateItem(array_merge($data, ["id_tags" => $id_tags]));
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

    private function sendFile(Spreadsheet $spreadsheet): void
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
        $file = $request->file("items")->getFileInfo()->getRealPath();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file);
        $importer = new ExcelImporter();

        return response()->json($importer->import($spreadsheet), 201);
    }
}
