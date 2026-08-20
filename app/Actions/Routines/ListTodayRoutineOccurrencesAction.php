<?php

namespace App\Actions\Routines;

use App\Models\Routine;
use App\Models\RoutineOccurrence;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class ListTodayRoutineOccurrencesAction
{
    public function __construct(private GenerateRoutineOccurrencesAction $generateOccurrences) {}

    /** @return Collection<int, RoutineOccurrence> */
    public function handle(User $user): Collection
    {
        Gate::forUser($user)->authorize('viewAny', Routine::class);

        Routine::query()->whereBelongsTo($user)->with('user')->each(
            fn (Routine $routine) => $this->generateOccurrences->handle($routine),
        );

        return RoutineOccurrence::query()
            ->whereHas('routine', fn ($query) => $query->whereBelongsTo($user))
            ->with('routine')
            ->orderBy('scheduled_for')
            ->get()
            ->filter(fn (RoutineOccurrence $occurrence) => $occurrence->local_date->isSameDay(now($occurrence->routine->timezone)))
            ->values();
    }
}
