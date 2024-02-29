<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use App\Services\ExcelImporter\ExcelImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class ExcelImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_import(): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();
        $newData = [
            'name' => 'New name',
            'category' => 'New category',
            'tags' => 'New tag. New new tag'
        ];
        $activeSheet->setCellValue('A2', $newData['name']);
        $activeSheet->setCellValue('B2', $newData['category']);
        $activeSheet->setCellValue('C2', $newData['tags']);
        $item = Item::factory()
            ->hasAttached(Tag::factory(3))
            ->for(Category::factory(), "category")
            ->create();
        $prepareTags = [];
        foreach ($item->tags as $tag) {
            $prepareTags[] = $tag->name;
        }
        asort($prepareTags);
        $tagNames = implode(" ", $prepareTags);
        $activeSheet->setCellValue('A3', $item->name);
        $activeSheet->setCellValue('B3', $item->category->name);
        $activeSheet->setCellValue('C3', $tagNames);
        $notValidItem = [
            'name' => '#',
            'category' => 'new category',
            'tag' => 'new tag'
        ];
        $notValidCategory = [
            'name' => 'new item name',
            'category' => '#',
            'tag' => 'new tag'
        ];
        $notValidTag = [
            'name' => 'new item name',
            'category' => 'new category',
            'tag' => '#'
        ];
        $activeSheet->setCellValue('A4', $notValidItem['name']);
        $activeSheet->setCellValue('B4', $notValidItem['category']);
        $activeSheet->setCellValue('C4', $notValidItem['tag']);
        $activeSheet->setCellValue('A5', $notValidCategory['name']);
        $activeSheet->setCellValue('B5', $notValidCategory['category']);
        $activeSheet->setCellValue('C5', $notValidCategory['tag']);
        $activeSheet->setCellValue('A6', $notValidTag['name']);
        $activeSheet->setCellValue('B6', $notValidTag['category']);
        $activeSheet->setCellValue('C6', $notValidTag['tag']);

        $excelImporter = new ExcelImporter();
        $result = $excelImporter->import($spreadsheet);

        $this->assertDatabaseHas('items', ['name' => $newData['name']]);
        $this->assertDatabaseHas('categories', ['name' => $newData['category']]);
        foreach ($prepareTags as $tag) {
            $this->assertDatabaseHas('tags', ['name' => $tag]);
        }
        $this->assertEquals($result->existingItemRow[0], 3);
        $this->assertEquals($result->notValidatedItemRow[0], 4);
        $this->assertEquals($result->notValidatedItemRow[1], 5);
        $this->assertEquals($result->notValidatedItemRow[2], 6);
    }
}
