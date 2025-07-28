<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EmpresaParceira;
use App\Models\Contrato;
use App\Models\Agenda;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'nome' => 'Administrador',
            'email' => 'admin@progmud.com',
            'funcao' => 'admin',
        ]);

        $techLeads = User::factory(2)->create(['funcao' => 'techlead']);
        $coordenadores = User::factory(2)->create(['funcao' => 'coordenador_operacoes']);
        $consultores = User::factory(10)->create(['funcao' => 'consultor']);
        $clientes = EmpresaParceira::factory(5)->create();

        $clientes->each(function ($cliente) use ($coordenadores, $techLeads) {
            Contrato::factory(2)->create([
                'cliente_id' => $cliente->id,
                'coordenador_id' => $coordenadores->random()->id,
                'tech_lead_id' => $techLeads->random()->id,
            ]);
        });

        $contratos = Contrato::all();
        $consultores->each(function ($consultor) use ($contratos) {
            Agenda::factory(5)->create([
                'consultor_id' => $consultor->id,
                'contrato_id' => $contratos->random()->id,
            ]);
        });
    }
}