<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EmpresaParceira;
use App\Models\Projeto;
use App\Models\Agenda;
use App\Models\Apontamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar Usuários
        $admin = User::create([
            'nome' => 'Admin', 'sobrenome' => 'Progmud', 'email' => 'admin@progmud.com',
            'password' => Hash::make('password'), 'funcao' => 'admin', 'status' => 'Ativo',
            'termos_aceite_em' => now(), 'ip_aceite' => '127.0.0.1'
        ]);

        $techLead1 = User::create([
            'nome' => 'Carlos', 'sobrenome' => 'Silva', 'email' => 'carlos.silva@progmud.com',
            'password' => Hash::make('password'), 'funcao' => 'techlead', 'status' => 'Ativo',
            'termos_aceite_em' => now(), 'ip_aceite' => '127.0.0.1'
        ]);

        $techLead2 = User::create([
            'nome' => 'Mariana', 'sobrenome' => 'Almeida', 'email' => 'mariana.almeida@progmud.com',
            'password' => Hash::make('password'), 'funcao' => 'techlead', 'status' => 'Ativo',
            'termos_aceite_em' => now(), 'ip_aceite' => '127.0.0.1'
        ]);

        $consultor1 = User::create(['nome' => 'João', 'sobrenome' => 'Pereira', 'email' => 'joao.pereira@progmud.com', 'password' => Hash::make('password'), 'funcao' => 'consultor', 'status' => 'Ativo', 'termos_aceite_em' => now()]);
        $consultor2 = User::create(['nome' => 'Ana', 'sobrenome' => 'Souza', 'email' => 'ana.souza@progmud.com', 'password' => Hash::make('password'), 'funcao' => 'consultor', 'status' => 'Ativo', 'termos_aceite_em' => now()]);
        $consultor3 = User::create(['nome' => 'Lucas', 'sobrenome' => 'Ferreira', 'email' => 'lucas.ferreira@progmud.com', 'password' => Hash::make('password'), 'funcao' => 'consultor', 'status' => 'Inativo', 'termos_aceite_em' => now()]);
        $consultor4 = User::create(['nome' => 'Beatriz', 'sobrenome' => 'Costa', 'email' => 'beatriz.costa@progmud.com', 'password' => Hash::make('password'), 'funcao' => 'consultor', 'status' => 'Ativo', 'termos_aceite_em' => now()]);
        $consultor5 = User::create(['nome' => 'Rafael', 'sobrenome' => 'Santos', 'email' => 'rafael.santos@progmud.com', 'password' => Hash::make('password'), 'funcao' => 'consultor', 'status' => 'Ativo', 'termos_aceite_em' => now()]);

        // 2. Criar Clientes (Empresas Parceiras)
        $cliente1 = EmpresaParceira::create(['nome_empresa' => 'Inovatech Soluções', 'cnpj' => '11.222.333/0001-44', 'saldo_horas' => 80, 'status' => 'Ativo']);
        $cliente2 = EmpresaParceira::create(['nome_empresa' => 'Nexus Contabilidade', 'cnpj' => '22.333.444/0001-55', 'saldo_horas' => 120, 'status' => 'Ativo']);
        $cliente3 = EmpresaParceira::create(['nome_empresa' => 'Alfa Transportes', 'cnpj' => '33.444.555/0001-66', 'saldo_horas' => 8, 'status' => 'Inativo']);

        // 3. Criar Projetos
        $projeto1 = Projeto::create(['nome_projeto' => 'Implantação ERP', 'empresa_parceira_id' => $cliente1->id, 'tech_lead_id' => $techLead1->id, 'status' => 'Em Andamento']);
        $projeto2 = Projeto::create(['nome_projeto' => 'Desenvolvimento App', 'empresa_parceira_id' => $cliente1->id, 'tech_lead_id' => $techLead2->id, 'status' => 'Em Andamento']);
        $projeto3 = Projeto::create(['nome_projeto' => 'Suporte Fiscal', 'empresa_parceira_id' => $cliente2->id, 'tech_lead_id' => $techLead1->id, 'status' => 'Concluído']);
        $projeto4 = Projeto::create(['nome_projeto' => 'Migração de Dados', 'empresa_parceira_id' => $cliente3->id, 'tech_lead_id' => $techLead2->id, 'status' => 'Pausado']);

        // 4. Relação N-para-N: Ligar Consultores a Tech Leads
        $consultor1->techLeads()->sync([$techLead1->id, $techLead2->id]);
        $consultor2->techLeads()->sync([$techLead1->id]);
        $consultor4->techLeads()->sync([$techLead2->id]);
        $consultor5->techLeads()->sync([$techLead1->id, $techLead2->id]);
        
        // 5. Criar Agendas e Apontamentos
        $agenda1 = Agenda::create(['projeto_id' => $projeto1->id, 'consultor_id' => $consultor1->id, 'data_hora' => now()->addDays(2)->setHour(10), 'assunto' => 'Reunião de Alinhamento', 'status' => 'Agendada']);
        $agenda2 = Agenda::create(['projeto_id' => $projeto2->id, 'consultor_id' => $consultor2->id, 'data_hora' => now()->subDays(1)->setHour(14), 'assunto' => 'Treinamento Usuários', 'status' => 'Realizada']);
        Apontamento::create(['agenda_id' => $agenda2->id, 'consultor_id' => $consultor2->id, 'horas_trabalhadas' => 4, 'descricao_atividades' => 'Treinamento realizado com a equipe do cliente.', 'status_aprovacao' => 'aprovado', 'aprovado_por' => $techLead2->id, 'aprovado_em' => now()]);
        
        $agenda3 = Agenda::create(['projeto_id' => $projeto3->id, 'consultor_id' => $consultor4->id, 'data_hora' => now()->addDays(5)->setHour(16), 'assunto' => 'Ajustes Fiscais', 'status' => 'Agendada']);
    }
}