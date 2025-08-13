<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sugestao extends Model
{
    use HasFactory;

    /**
     * Informa ao Laravel o nome correto da tabela.
     */
    protected $table = 'sugestoes';

    /**
     * The attributes that are mass assignable.
     *
     * CORREÇÃO: Adicionado 'status' à lista para permitir que seja atualizado.
     */
    protected $fillable = [
        'titulo',
        'descricao',
        'status', 
        'usuario_id'
    ];

    /**
     * Get the user that owns the suggestion.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
