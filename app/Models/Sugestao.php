<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sugestao extends Model
{
    use HasFactory;

    protected $table = 'sugestoes';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'descricao',
        'status',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
