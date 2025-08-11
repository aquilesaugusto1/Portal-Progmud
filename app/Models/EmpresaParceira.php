<?php

namespace App\Models;

use App\Traits\Userstamps; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaParceira extends Model
{
    use HasFactory, Userstamps; 

    protected $table = 'empresas_parceiras';

    protected $fillable = [
        'nome_empresa',
        'cnpj',
        'saldo_horas',
        'status',
        'endereco_completo',
        'contato_principal',
        'contato_comercial',
        'contato_financeiro',
        'contato_tecnico',
    ];

    protected function casts(): array
    {
        return [
            'endereco_completo' => 'array',
            'contato_principal' => 'array',
            'contato_comercial' => 'array',
            'contato_financeiro' => 'array',
            'contato_tecnico' => 'array',
        ];
    }

    /**
     * Get the contracts for the partner company.
     */
    public function contratos() 
    {
        return $this->hasMany(Contrato::class, 'cliente_id');
    }
}