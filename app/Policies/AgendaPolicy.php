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

    public function viewAny(User $user): bool
    {
        return in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico', 'techlead']);
    }

    public function view(User $user, Agenda $agenda): bool
    {
        if (in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico', 'techlead'])) {
            return true;
        }

        return $user->id === $agenda->consultor_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico', 'techlead']);
    }

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
