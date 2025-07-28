<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importe a classe correta aqui

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'sobrenome',
        'email',
        'email_totvs_partner',
        'password',
        'status',
        'funcao',
        'tipo_contrato',
        'data_nascimento',
        'nacionalidade',
        'naturalidade',
        'endereco',
        'cargo',
        'nivel',
        'dados_empresa_prestador',
        'dados_bancarios',
        'termos_aceite_em',
        'ip_aceite',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'endereco' => 'array',
            'dados_empresa_prestador' => 'array',
            'dados_bancarios' => 'array',
            'data_nascimento' => 'date',
            'termos_aceite_em' => 'datetime',
        ];
    }

    public function techLeads(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'colaborador_tech_lead', 'consultor_id', 'tech_lead_id');
    }

    public function consultores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'colaborador_tech_lead', 'tech_lead_id', 'consultor_id');
    }

    public function apontamentos(): HasMany
    {
        return $this->hasMany(Apontamento::class, 'consultor_id');
    }
}