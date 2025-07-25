<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Projeto;
use App\Models\EmpresaParceira;
use App\Models\Consultor;
use App\Models\User;

class ProjetoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa1 = EmpresaParceira::where('nome_empresa', 'Cliente Alfa')->first();
        $empresa2 = EmpresaParceira::where('nome_empresa', 'Cliente Beta')->first();
        
        $consultor1 = Consultor::where('email', 'pedro.martins@consultor.com')->first();
        $consultor2 = Consultor::where('email', 'lucas.andrade@consultor.com')->first();
        
        $techLead1 = User::where('email', 'maria.silva@techlead.com')->first();

        $projeto1 = Projeto::create([
            'nome_projeto' => 'Implantação CRM',
            'empresa_parceira_id' => $empresa1->id,
            'tipo' => 'act',
        ]);
        $projeto1->consultores()->sync([$consultor1->id, $consultor2->id]);
        
        $projeto2 = Projeto::create([
            'nome_projeto' => 'Manutenção Sistema Legado',
            'empresa_parceira_id' => $empresa2->id,
            'tipo' => 'act+',
        ]);
        $projeto2->consultores()->sync([$consultor2->id]);
        $projeto2->techLeads()->sync([$techLead1->id]);
    }
}
