<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class SheetListImport implements WithMultipleSheets, SkipsUnknownSheets
{
    private $sheetNames = [];

    public function sheets(): array
    {
        return [];
    }

    public function onUnknownSheet($sheetName)
    {
        $this->sheetNames[] = $sheetName;
    }

    public function getSheetNames(): array
    {
        return $this->sheetNames;
    }
}
