<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apontamento extends Model
{
    use HasFactory;

    protected $table = 'apontamentos';

    protected $fillable = [
        'agenda_id',
        'consultor_id',
        'horas_gastas',
        'descricao',
        'faturado',
        'data_apontamento',
        'hora_inicio',
        'hora_fim',
    ];

    protected $casts = [
        'data_apontamento' => 'date',
        'faturado' => 'boolean',
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class);
    }

    public function consultor()
    {
        return $this->belongsTo(Consultor::class);
    }
}
