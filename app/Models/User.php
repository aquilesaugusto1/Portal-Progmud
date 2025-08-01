<?php

namespace App\Models;

use App\Traits\Userstamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Userstamps;

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
        'created_by', // Adicionado para a trait
        'updated_by', // Adicionado para a trait
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

    public function consultoresLiderados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'colaborador_tech_lead', 'tech_lead_id', 'consultor_id')
                    ->select('usuarios.*');
    }

    public function apontamentos(): HasMany
    {
        return $this->hasMany(Apontamento::class, 'consultor_id');
    }

    /**
     * The contracts that the user belongs to.
     */
    public function contratos(): BelongsToMany
    {
        return $this->belongsToMany(Contrato::class, 'contrato_usuario', 'usuario_id', 'contrato_id')
                    ->withPivot('funcao_contrato')
                    ->withTimestamps();
    }

    // Métodos de verificação de função (ROLE CHECKING) - ADICIONADO AQUI
    public function isAdmin()
    {
        return $this->funcao === 'admin';
    }

    public function isCoordenador()
    {
        return str_contains($this->funcao, 'coordenador');
    }

    public function isTechLead()
    {
        return $this->funcao === 'techlead';
    }

    public function isConsultor()
    {
        return $this->funcao === 'consultor';
    }
}
