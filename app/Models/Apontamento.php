<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apontamento extends Model
{
    use HasFactory;

    protected $table = 'apontamentos';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'consultor_id',
        'agenda_id',
        'contrato_id',
        'data_apontamento',
        'hora_inicio',
        'hora_fim',
        'horas_gastas',
        'descricao',
        'caminho_anexo',
        'status',
        'faturavel',
        'aprovado_por_id',
        'data_aprovacao',
        'motivo_rejeicao',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_apontamento' => 'date',
        'faturavel' => 'boolean',
        'data_aprovacao' => 'datetime',
    ];

    /**
     * Get the agenda for the time entry.
     */
    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    /**
     * Get the consultant for the time entry.
     */
    public function consultor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultor_id');
    }

    /**
     * Get the contract for the time entry.
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Get the user who approved the time entry.
     */
    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por_id');
    }
}
