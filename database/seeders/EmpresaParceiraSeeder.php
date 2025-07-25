<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaParceira;

class EmpresaParceiraSeeder extends Seeder
{
    public function run(): void
    {
        EmpresaParceira::create([
            'nome_empresa' => 'Cliente Alfa',
            'contato_principal' => 'Carlos Pereira',
            'email' => 'contato@alfa.com',
            'horas_contratadas' => 100,
        ]);

        EmpresaParceira::create([
            'nome_empresa' => 'Cliente Beta',
            'contato_principal' => 'Ana Costa',
            'email' => 'contato@beta.com',
            'horas_contratadas' => 150,
        ]);
    }
}
