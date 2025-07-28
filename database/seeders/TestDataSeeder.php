<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EmpresaParceira;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar um Admin Padrão
        User::create([
            'nome' => 'Admin',
            'sobrenome' => 'Agen',
            'email' => 'admin@agen.com',
            'password' => Hash::make('password'),
            'funcao' => 'admin',
            'status' => 'Ativo',
            'termos_aceite_em' => now(),
            'ip_aceite' => '127.0.0.1',
        ]);

        // Criar Coordenadores
        User::create([
            'nome' => 'Carlos',
            'sobrenome' => 'Coordenador',
            'email' => 'carlos.coord@agen.com',
            'password' => Hash::make('password'),
            'funcao' => 'coordenador_operacoes',
            'status' => 'Ativo',
            'termos_aceite_em' => now(),
            'ip_aceite' => '127.0.0.1',
        ]);
         User::create([
            'nome' => 'Beatriz',
            'sobrenome' => 'Coordenadora',
            'email' => 'beatriz.coord@agen.com',
            'password' => Hash::make('password'),
            'funcao' => 'coordenador_tecnico',
            'status' => 'Ativo',
            'termos_aceite_em' => now(),
            'ip_aceite' => '127.0.0.1',
        ]);

        // Criar Tech Leads
        User::factory()->create([
            'nome' => 'Fernanda',
            'sobrenome' => 'TechLead',
            'email' => 'fernanda.tl@agen.com',
            'funcao' => 'techlead',
            'status' => 'Ativo',
        ]);
        User::factory()->create([
            'nome' => 'Ricardo',
            'sobrenome' => 'TechLead',
            'email' => 'ricardo.tl@agen.com',
            'funcao' => 'techlead',
            'status' => 'Ativo',
        ]);

        // Criar Consultores
        User::factory()->count(5)->create([
            'funcao' => 'consultor',
            'status' => 'Ativo',
        ]);

        // Criar Empresas Parceiras
        EmpresaParceira::factory()->count(5)->create();
    }
}
