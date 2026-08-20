<?php

namespace App\Actions\Routines;

use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PauseRoutineAction
{
    public function __construct(private GenerateRoutineOccurrencesAction $generateOccurrences) {}

    public function handle(User $user, Routine $routine): Routine
    {
        Gate::forUser($user)->authorize('update', $routine);
        $this->generateOccurrences->handle($routine);
        $routine->forceFill(['status' => RoutineStatus::Paused])->save();

        return $routine->refresh();
    }
}
