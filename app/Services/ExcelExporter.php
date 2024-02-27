<?php

namespace App\Services;

use App\Models\Item;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class ExcelExporter
{
    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();
        $this->prepareColumns($activeSheet);
        $items = Item::with(["category", "tags"])->get()->all();
        $i = 2;
        foreach ($items as $item) {
            $prepareTags = [];
            $activeSheet->setCellValue("A" . $i, $item->id);
            $activeSheet->setCellValue("B" . $i, $item->name);
            $activeSheet->setCellValue("C" . $i, $item->category->name);
            foreach ($item->tags as $tag) {
                $prepareTags[] = $tag->name;
            }
            $activeSheet->setCellValue("D" . $i, implode(" ", $prepareTags));
            $i++;
        }

        return $spreadsheet;
    }

    public function columnNameStyle(Worksheet $activeSheet , string $columnName): void
    {
        $activeSheet->getStyle($columnName)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);
    }

    public function prepareColumns(Worksheet $activeSheet): void
    {
        $activeSheet->setCellValue('A1', 'ID');
        $activeSheet->setCellValue('B1', 'Наименование');
        $activeSheet->setCellValue('C1', 'Категория');
        $activeSheet->setCellValue('D1', 'Метки');
        $activeSheet->getColumnDimension('B')->setWidth(20);
        $activeSheet->getColumnDimension('C')->setWidth(20);
        $activeSheet->getColumnDimension('D')->setWidth(50);
        $this->columnNameStyle($activeSheet, "A1");
        $this->columnNameStyle($activeSheet, "B1");
        $this->columnNameStyle($activeSheet, "C1");
        $this->columnNameStyle($activeSheet, "D1");
    }
}
