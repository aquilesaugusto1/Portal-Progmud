<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Consultor;

class ConsultorSeeder extends Seeder
{
    public function run(): void
    {
        $techLead1 = User::where('email', 'maria.silva@techlead.com')->first();
        $techLead2 = User::where('email', 'joao.souza@techlead.com')->first();
        
        $consultoresData = [
            [
                'nome' => 'Pedro Martins',
                'email' => 'pedro.martins@consultor.com',
                'tech_leads' => [$techLead1->id]
            ],
            [
                'nome' => 'Lucas Andrade',
                'email' => 'lucas.andrade@consultor.com',
                'tech_leads' => [$techLead1->id, $techLead2->id]
            ],
            [
                'nome' => 'Carla Dias',
                'email' => 'carla.dias@consultor.com',
                'tech_leads' => [$techLead2->id]
            ]
        ];

        foreach ($consultoresData as $data) {
            DB::transaction(function () use ($data) {
                $user = User::create([
                    'nome' => $data['nome'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'funcao' => 'consultor',
                ]);

                $consultor = new Consultor([
                    'nome' => $data['nome'],
                    'email' => $data['email'],
                ]);
                $consultor->usuario_id = $user->id;
                $consultor->save();
                $consultor->techLeads()->sync($data['tech_leads']);
            });
        }
    }
}
