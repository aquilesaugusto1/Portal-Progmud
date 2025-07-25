<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AlocacoesImport;
use App\Imports\SheetListImport;
use App\Services\SpreadsheetAnalyzer;

class ImportController extends Controller
{
    public function create()
    {
        return view('imports.create');
    }

    public function selectSheet(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls|max:10240']);
        $path = $request->file('file')->store('imports');
        
        $sheetListImport = new SheetListImport();
        Excel::import($sheetListImport, $path);
        $sheetNames = $sheetListImport->getSheetNames();

        $request->session()->put('import_file_path', $path);
        return view('imports.select_sheet', compact('sheetNames'));
    }

    public function showMapping(Request $request, SpreadsheetAnalyzer $analyzer)
    {
        $request->validate(['sheet_name' => 'required|string']);
        $path = $request->session()->get('import_file_path');
        $sheetName = $request->input('sheet_name');

        if (!$path || !file_exists(storage_path('app/' . $path))) {
            return redirect()->route('imports.create')->with('error', 'Arquivo de importação expirou. Por favor, envie novamente.');
        }
        
        list($headings, $headerRowIndex) = $analyzer->analyze(storage_path('app/' . $path), $sheetName);

        if (empty($headings)) {
            return redirect()->route('imports.create')->with('error', "Não foram encontrados cabeçalhos na planilha '{$sheetName}'. Verifique se a planilha tem dados e tente novamente.");
        }
        
        $request->session()->put('import_sheet_name', $sheetName);
        $request->session()->put('import_header_row_index', $headerRowIndex);

        $dbColumns = [
            'consultor_id' => 'Nome do Consultor',
            'projeto_id' => 'Nome do Projeto',
            'data_inicio' => 'Data de Início',
            'data_fim' => 'Data de Fim',
            'tipo_alocacao' => 'Tipo de Alocação'
        ];

        return view('imports.mapping', compact('headings', 'dbColumns'));
    }

    public function processMapping(Request $request)
    {
        $request->validate(['mappings' => 'required|array']);
        $path = $request->session()->pull('import_file_path');
        $sheetName = $request->session()->pull('import_sheet_name');
        $headerRowIndex = $request->session()->pull('import_header_row_index');
        $mappings = $request->input('mappings');

        if (!$path || !$sheetName || $headerRowIndex === null) {
            return redirect()->route('imports.create')->with('error', 'Sessão de importação expirou. Por favor, comece novamente.');
        }

        try {
            $headerRowNumber = $headerRowIndex + 1;
            Excel::import(new AlocacoesImport($mappings, $sheetName, $headerRowNumber), $path);
            return redirect()->route('agendas.index')->with('success', 'Alocações importadas com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('imports.create')->with('error', 'Ocorreu um erro na importação: ' . $e->getMessage());
        }
    }
}
