<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SheetListImport implements SkipsUnknownSheets, WithMultipleSheets
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
