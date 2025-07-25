<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmpresaParceira extends Model
{
    use HasFactory;

    protected $table = 'empresas_parceiras';

    protected $fillable = [
        'nome_empresa',
        'contato_principal',
        'telefone',
        'email',
        'ramo_atividade',
        'horas_contratadas',
    ];

    public function projetos()
    {
        return $this->hasMany(Projeto::class);
    }

    public function apontamentosAprovados()
    {
        
        return Apontamento::where('status', 'Aprovado')
            ->where('faturado', true) 
            ->whereHas('agenda.projeto', function ($query) {
                $query->where('empresa_parceira_id', $this->id);
            });
    }

    public function getHorasGastasAttribute()
    {
        return $this->apontamentosAprovados()->sum(DB::raw('ABS(horas_gastas)'));
    }

    public function getSaldoTotalAttribute()
    {
        return $this->horas_contratadas - $this->getHorasGastasAttribute();
    }
}