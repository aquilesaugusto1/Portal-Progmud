<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgendaPolicy
{
    public function viewAlocacao(User $user): bool
    {
        return $user->funcao === 'admin' || $user->funcao === 'techlead';
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Agenda $agenda): bool
    {
        if ($user->funcao === 'admin' || $user->funcao === 'techlead') {
            return true;
        }

        return $user->consultor && $user->consultor->id === $agenda->consultor_id;
    }

    public function create(User $user): bool
    {
        return $user->funcao === 'admin' || $user->funcao === 'techlead';
    }

    public function update(User $user, Agenda $agenda): bool
    {
        if ($user->funcao === 'admin') {
            return true;
        }

        if ($user->funcao === 'techlead') {
            return $user->consultoresLiderados()->where('consultores.id', $agenda->consultor_id)->exists();
        }

        if ($user->funcao === 'consultor') {
            return $user->consultor && $user->consultor->id === $agenda->consultor_id;
        }

        return false;
    }

    public function delete(User $user, Agenda $agenda): bool
    {
        if ($user->funcao === 'admin') {
            return true;
        }

        if ($user->funcao === 'techlead') {
            return $user->consultoresLiderados()->where('consultores.id', $agenda->consultor_id)->exists();
        }
        
        return false;
    }
}
