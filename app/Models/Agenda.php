<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultor_id',
        'projeto_id',
        'data_hora',
        'assunto',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_hora' => 'datetime',
        ];
    }

    public function consultor()
    {
        // Importante: Aponte para o modelo User
        return $this->belongsTo(User::class, 'consultor_id');
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }
}