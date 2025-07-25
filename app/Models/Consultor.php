<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function techLeads()
    {
        return $this->belongsToMany(User::class, 'consultor_tech_lead', 'consultor_id', 'tech_lead_id');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }

    public function apontamentos()
    {
        return $this->hasMany(Apontamento::class);
    }
    
    public function projetos()
    {
        return $this->belongsToMany(Projeto::class, 'projeto_consultor');
    }
}