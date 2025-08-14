<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'data_hora',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_hora' => 'datetime',
    ];

    /**
     * Get the consultant for the agenda.
     */
    public function consultor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultor_id');
    }

    /**
     * Get the contract for the agenda.
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Get the time entry for the agenda.
     */
    public function apontamento(): HasOne
    {
        return $this->hasOne(Apontamento::class);
    }
}
