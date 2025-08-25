<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CpTotvs extends Model
{
    use HasFactory;

    protected $table = 'cp_totvs';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'status',
        'user_creator_id',
        'user_updater_id',
    ];

    public function contratos(): BelongsToMany
    {
        return $this->belongsToMany(Contrato::class, 'contrato_cp_totvs', 'cp_totvs_id', 'contrato_id');
    }
}