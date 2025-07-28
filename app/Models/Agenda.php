<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultor_id',
        'contrato_id',
        'titulo',
        'descricao',
        'data_hora_inicio',
        'data_hora_fim',
        'status',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
    ];

    public function consultor()
    {
        return $this->belongsTo(User::class, 'consultor_id');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function apontamento()
    {
        return $this->hasOne(Apontamento::class);
    }
}
