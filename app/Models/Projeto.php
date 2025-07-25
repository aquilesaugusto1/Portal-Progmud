<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_projeto',
        'empresa_parceira_id',
        'tipo',
    ];

    public function empresaParceira()
    {
        return $this->belongsTo(EmpresaParceira::class);
    }

    public function consultores()
    {
        return $this->belongsToMany(Consultor::class, 'projeto_consultor');
    }

    public function techLeads()
    {
        return $this->belongsToMany(User::class, 'projeto_tech_lead', 'projeto_id', 'tech_lead_id');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }
}
