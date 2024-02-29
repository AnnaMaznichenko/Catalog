<?php

namespace App\Services\ExcelImporter;

class ImportResult
{
    public array $newItems = [];
    public array $existingItemRow = [];
    public array $notValidatedItemRow = [];
}
