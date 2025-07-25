<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_hora',
        'assunto',
        'status',
        'consultor_id',
        'projeto_id',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    public function consultor()
    {
        return $this->belongsTo(Consultor::class);
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }

    public function apontamento()
    {
        return $this->hasOne(Apontamento::class);
    }
}
