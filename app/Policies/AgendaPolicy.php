<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgendaPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->funcao === 'admin') {
            return true;
        }
 
        return null;
    }

    /**
     * Determine whether the user can view any models.
     * Ação: Acessar a página da lista de agendas.
     * Regra: Qualquer usuário logado pode acessar a página. O controller fará o filtro.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Ação: Ver os detalhes de uma agenda específica.
     */
    public function view(User $user, Agenda $agenda): bool
    {
        if (in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico', 'techlead'])) {
            return true;
        }

        return $user->id === $agenda->consultor_id;
    }

    /**
     * Determine whether the user can create models.
     * Ação: Criar uma nova agenda.
     */
    public function create(User $user): bool
    {
        return in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico', 'techlead']);
    }

    /**
     * Determine whether the user can update the model.
     * Ação: Editar uma agenda existente.
     */
    public function update(User $user, Agenda $agenda): bool
    {
        if (in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico'])) {
            return true;
        }

        if ($user->funcao === 'techlead') {
            return $user->consultoresLiderados()->where('id', $agenda->consultor_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Ação: Excluir uma agenda.
     */
    public function delete(User $user, Agenda $agenda): bool
    {
        if (in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico'])) {
            return true;
        }

        if ($user->funcao === 'techlead') {
            return $user->consultoresLiderados()->where('id', $agenda->consultor_id)->exists();
        }

        return false;
    }
}
