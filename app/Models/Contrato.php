<?php

namespace App\Models;

use App\Traits\Userstamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory, Userstamps;

    protected $fillable = [
        'cliente_id',
        'numero_contrato',
        'tipo_contrato',
        'produtos',
        'especifique_outro',
        'status',
        'data_inicio',
        'data_termino',
        'contato_principal',
        'baseline_horas_mes',
        'baseline_horas_original',
        'permite_antecipar_baseline',
        'documento_baseline_path',
    ];

    protected $casts = [
        'produtos' => 'array',
        'data_inicio' => 'date',
        'data_termino' => 'date',
        'permite_antecipar_baseline' => 'boolean',
        'baseline_horas_mes' => 'decimal:2',
        'baseline_horas_original' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(EmpresaParceira::class, 'cliente_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'contrato_usuario', 'contrato_id', 'usuario_id')
                        ->withPivot('funcao_contrato')
                        ->withTimestamps();
    }

    public function coordenadores()
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'coordenador');
    }

    public function techLeads()
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'tech_lead');
    }

    public function consultores()
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'consultor');
    }
}