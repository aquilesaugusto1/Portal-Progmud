<?php

namespace App\Models;

use App\Traits\Userstamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory, Userstamps;

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
     * The users that belong to the contract.
     */
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'contrato_usuario', 'contrato_id', 'usuario_id')
                        ->withPivot('funcao_contrato')
                        ->withTimestamps();
    }

    /**
     * Get the coordinators for the contract.
     */
    public function coordenadores()
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'coordenador');
    }

    /**
     * Get the tech leads for the contract.
     */
    public function techLeads()
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'tech_lead');
    }

    /**
     * Get the consultants for the contract.
     */
    public function consultores()
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'consultor');
    }
}
