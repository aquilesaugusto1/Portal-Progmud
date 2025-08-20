<?php

namespace App\Models;

use Database\Factories\AgendaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static AgendaFactory factory(...$parameters)
 */
class Agenda extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'consultor_id',
        'contrato_id',
        'assunto',
        'descricao',
        'data',
        'hora_inicio',
        'hora_fim',
        'status',
        'faturavel',
        'tipo_periodo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'date',
        'faturavel' => 'boolean',
    ];

    /**
     * Get the consultant for the agenda.
     * @return BelongsTo<User, Agenda>
     */
    public function consultor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultor_id');
    }

    /**
     * Get the contract for the agenda.
     * @return BelongsTo<Contrato, Agenda>
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Get the time entry for the agenda.
     * @return HasOne<Apontamento, Agenda>
     */
    public function apontamento(): HasOne
    {
        return $this->hasOne(Apontamento::class);
    }
}
