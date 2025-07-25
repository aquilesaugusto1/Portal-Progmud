<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nome' => 'Admin Agen',
            'email' => 'admin@agen.com',
            'password' => Hash::make('password'),
            'funcao' => 'admin',
        ]);

        User::create([
            'nome' => 'Maria Silva (Tech Lead)',
            'email' => 'maria.silva@techlead.com',
            'password' => Hash::make('password'),
            'funcao' => 'techlead',
        ]);

        User::create([
            'nome' => 'João Souza (Tech Lead)',
            'email' => 'joao.souza@techlead.com',
            'password' => Hash::make('password'),
            'funcao' => 'techlead',
        ]);
    }
}
