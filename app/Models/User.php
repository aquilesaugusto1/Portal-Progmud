<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'subordinado_a',
        'dados_empresa_prestador',
        'dados_bancarios',
        'termos_aceite_em',
        'ip_aceite', // Adicione esta linha
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

    public function superior()
    {
        return $this->belongsTo(User::class, 'subordinado_a');
    }
}