<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use App\Services\ExcelExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExcelExporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_export(): void
    {
        $items = Item::factory(rand(1, 10))
            ->hasAttached(Tag::factory(3))
            ->for(Category::factory(), "category")
            ->create();
        $excelExporter = new ExcelExporter();
        $spreadsheet = $excelExporter->export();
        $offset = 2;
        foreach ($items as $i => $item) {
            $categoryName = $item->category->name;
            $prepareTags = [];
            foreach ($item->tags as $tag) {
                $prepareTags[] = $tag->name;
            }
            asort($prepareTags);
            $tagNames = implode(" ", $prepareTags);
            $name = $spreadsheet->getActiveSheet()->getCell("B" . ($i + $offset))->getCalculatedValue();
            $category = $spreadsheet->getActiveSheet()->getCell("C" . ($i + $offset))->getCalculatedValue();
            $tags = $spreadsheet->getActiveSheet()->getCell("D" . ($i + $offset))->getCalculatedValue();
            $this->assertEquals($name, $item->name);
            $this->assertEquals($category, $categoryName);
            $this->assertEquals($tags, $tagNames);
        }
    }
}
