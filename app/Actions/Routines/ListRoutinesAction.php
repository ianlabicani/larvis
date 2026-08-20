<?php

namespace App\Actions\Routines;

use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class ListRoutinesAction
{
    /** @return Collection<int, Routine> */
    public function handle(User $user, ?RoutineStatus $status = null): Collection
    {
        Gate::forUser($user)->authorize('viewAny', Routine::class);

        return Routine::query()
            ->whereBelongsTo($user)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['occurrences' => fn ($query) => $query->latest('local_date')->limit(30)])
            ->latest()
            ->get();
    }
}
