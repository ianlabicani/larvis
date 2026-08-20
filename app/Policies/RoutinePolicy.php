<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;

class RoutinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('routines.view');
    }

    public function view(User $user, Routine $routine): bool
    {
        return $user->can('routines.view') && $user->is($routine->user);
    }

    public function create(User $user): bool
    {
        return $user->can('routines.create');
    }

    public function update(User $user, Routine $routine): bool
    {
        return $user->can('routines.update') && $user->is($routine->user);
    }
}
