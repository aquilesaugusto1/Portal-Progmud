<?php

namespace App\Models;

use App\Traits\Userstamps;
use Database\Factories\ContratoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static ContratoFactory factory(...$parameters)
 */
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
        'possui_engenharia_valores', // Adicionado
    ];

    protected $casts = [
        'produtos' => 'array',
        'data_inicio' => 'date',
        'data_termino' => 'date',
        'permite_antecipar_baseline' => 'boolean',
        'baseline_horas_mes' => 'decimal:2',
        'baseline_horas_original' => 'decimal:2',
        'possui_engenharia_valores' => 'boolean', // Adicionado
    ];

    /**
     * @return BelongsTo<EmpresaParceira, Contrato>
     */
    public function empresaParceira(): BelongsTo
    {
        return $this->belongsTo(EmpresaParceira::class, 'cliente_id');
    }

    /**
     * @return BelongsToMany<User, Contrato>
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contrato_usuario', 'contrato_id', 'usuario_id')
            ->withPivot('funcao_contrato')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, Contrato>
     */
    public function coordenadores(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'coordenador');
    }

    /**
     * @return BelongsToMany<User, Contrato>
     */
    public function techLeads(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'tech_lead');
    }

    /**
     * @return BelongsToMany<User, Contrato>
     */
    public function consultores(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('funcao_contrato', 'consultor');
    }
}
