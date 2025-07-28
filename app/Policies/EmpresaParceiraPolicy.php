<?php

namespace App\Policies;

use App\Models\EmpresaParceira;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmpresaParceiraPolicy
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

    public function view(User $user, EmpresaParceira $empresaParceira): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, EmpresaParceira $empresaParceira): bool
    {
        return false;
    }

    public function delete(User $user, EmpresaParceira $empresaParceira): bool
    {
        return false;
    }

    public function toggleStatus(User $user, EmpresaParceira $empresaParceira): bool
    {
        return false;
    }
}
