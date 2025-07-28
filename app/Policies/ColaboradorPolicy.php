<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ColaboradorPolicy
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
        return in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico']);
    }

    public function view(User $user, User $model): bool
    {
        return in_array($user->funcao, ['coordenador_operacoes', 'coordenador_tecnico']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function toggleStatus(User $user, User $model): bool
    {
        return false;
    }
}