<?php

namespace App\Exports;

use App\Models\Apontamento;
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
            'Contrato ID',
            'Descrição',
            'Horas Gastas',
            'Status',
        ];
    }

    public function map($apontamento): array
    {
        return [
            \Carbon\Carbon::parse($apontamento->data_apontamento)->format('d/m/Y'),
            $apontamento->consultor->nome,
            $apontamento->contrato->empresaParceira->nome_empresa,
            $apontamento->contrato->id,
            $apontamento->descricao,
            number_format($apontamento->horas_gastas, 2, ',', '.'),
            $apontamento->status,
        ];
    }
}
