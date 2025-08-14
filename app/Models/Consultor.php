<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \App\Models\User $usuario
 */
class Consultor extends Model
{
    use HasFactory;

    protected $table = 'consultores';

    protected $fillable = [
        'usuario_id',
        'nome',
        'email',
        'telefone',
        'status',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function techLeads(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'consultor_tech_lead', 'consultor_id', 'tech_lead_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }

    public function apontamentos(): HasMany
    {
        return $this->hasMany(Apontamento::class);
    }
}
