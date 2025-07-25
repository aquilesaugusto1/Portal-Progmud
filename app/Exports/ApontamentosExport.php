<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ApontamentosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $apontamentos;
    protected $filtros;

    public function __construct(Collection $apontamentos, array $filtros)
    {
        $this->apontamentos = $apontamentos;
        $this->filtros = $filtros;
    }

    public function collection()
    {
        return $this->apontamentos;
    }

    public function headings(): array
    {
        return [
            'Data',
            'Consultor',
            'Cliente',
            'Projeto',
            'Assunto',
            'Horas Gastas',
            'Descrição',
        ];
    }

    public function map($apontamento): array
    {
        return [
            $apontamento->data_apontamento->format('d/m/Y'),
            $apontamento->consultor->nome,
            $apontamento->agenda->projeto->empresaParceira->nome_empresa,
            $apontamento->agenda->projeto->nome_projeto,
            $apontamento->agenda->assunto,
            $apontamento->horas_gastas,
            $apontamento->descricao,
        ];
    }
}