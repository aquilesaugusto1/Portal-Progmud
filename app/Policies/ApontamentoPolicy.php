<?php

namespace App\Policies;

use App\Models\Apontamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApontamentoPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->funcao === 'admin') {
            return true;
        }
 
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Apontamento $apontamento): bool
    {
        if (in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico'])) {
            return true;
        }

        if ($user->funcao === 'techlead') {
            return $user->consultoresLiderados()->where('id', $apontamento->consultor_id)->exists();
        }

        return $user->id === $apontamento->consultor_id;
    }

    public function create(User $user): bool
    {
        return $user->funcao === 'consultor';
    }

    public function update(User $user, Apontamento $apontamento): bool
    {
        return $user->id === $apontamento->consultor_id && $apontamento->status === 'Pendente';
    }

    public function delete(User $user, Apontamento $apontamento): bool
    {
        return $user->id === $apontamento->consultor_id && $apontamento->status === 'Pendente';
    }

    public function approve(User $user, Apontamento $apontamento): bool
    {
        if (in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico'])) {
            return true;
        }

        if ($user->funcao === 'techlead') {
            return $user->consultoresLiderados()->where('id', $apontamento->consultor_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view the approvals page link in the sidebar.
     */
    public function viewAprovacoes(User $user): bool
    {
        return in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico', 'techlead']);
    }
}
