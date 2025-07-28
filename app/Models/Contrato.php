<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cliente_id',
        'numero_contrato',
        'tipo_contrato',
        'produtos',
        'especifique_outro',
        'coordenador_id',
        'tech_lead_id',
        'status',
        'data_inicio',
        'data_termino',
        'contato_principal',
        'baseline_horas_mes',
        'permite_antecipar_baseline',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'produtos' => 'array',
        'data_inicio' => 'date',
        'data_termino' => 'date',
        'permite_antecipar_baseline' => 'boolean',
    ];

    /**
     * Get the client for the contract.
     */
    public function cliente()
    {
        return $this->belongsTo(EmpresaParceira::class, 'cliente_id');
    }

    /**
     * Get the coordinator for the contract.
     */
    public function coordenador()
    {
        return $this->belongsTo(User::class, 'coordenador_id');
    }

    /**
     * Get the tech lead for the contract.
     */
    public function techLead()
    {
        return $this->belongsTo(User::class, 'tech_lead_id');
    }
}
