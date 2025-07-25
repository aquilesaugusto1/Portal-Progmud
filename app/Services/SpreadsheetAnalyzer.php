<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetAnalyzer
{
    public function analyze(string $filePath, string $sheetName): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getSheetByName($sheetName);
        
        if (!$worksheet) {
            return [[], -1];
        }
        
        $rows = new Collection($worksheet->toArray(null, true, true, true));

        return $this->findHeaderRow($rows);
    }

    private function findHeaderRow(Collection $rows): array
    {
        $bestGuessIndex = -1;
        $maxStringCount = -1;

        foreach ($rows->take(20) as $index => $row) {
            $filteredRow = (new Collection($row))->filter(fn($cell) => $cell !== null && $cell !== '');
            if ($filteredRow->isEmpty()) {
                continue;
            }

            $stringCount = $filteredRow->filter(fn($cell) => is_string($cell) && !is_numeric($cell))->count();
            
            if ($stringCount >= $maxStringCount && $stringCount > $filteredRow->count() * 0.5) {
                $maxStringCount = $stringCount;
                $bestGuessIndex = $index;
            }
        }
        
        if ($bestGuessIndex !== -1) {
            $headerValues = array_values($rows[$bestGuessIndex]->filter()->toArray());
            $zeroBasedIndex = $bestGuessIndex - 1;
            return [$headerValues, $zeroBasedIndex];
        }
        
        return [[], -1];
    }
}
