<?php

namespace App\Policies;

use App\Models\CpTotvs;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CpTotvsPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCoordenador();
    }

    public function view(User $user, CpTotvs $cpTotvs): bool
    {
        return $user->isAdmin() || $user->isCoordenador();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCoordenador();
    }

    public function update(User $user, CpTotvs $cpTotvs): bool
    {
        return $user->isAdmin() || $user->isCoordenador();
    }

    public function delete(User $user, CpTotvs $cpTotvs): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, CpTotvs $cpTotvs): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, CpTotvs $cpTotvs): bool
    {
        return $user->isAdmin();
    }

    public function toggleStatus(User $user): bool
    {
        return $user->isAdmin();
    }
}