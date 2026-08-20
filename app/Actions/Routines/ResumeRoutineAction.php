<?php

namespace App\Actions\Routines;

use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ResumeRoutineAction
{
    public function __construct(private GenerateRoutineOccurrencesAction $generateOccurrences) {}

    public function handle(User $user, Routine $routine): Routine
    {
        Gate::forUser($user)->authorize('update', $routine);

        $yesterday = now($routine->timezone)->startOfDay()->subDay();
        $cursorDate = $routine->generation_cursor?->toDateString();
        if ($cursorDate === null || $cursorDate < $yesterday->toDateString()) {
            $routine->forceFill(['generation_cursor' => $yesterday->toDateString()]);
        }
        $routine->forceFill(['status' => RoutineStatus::Active])->save();

        $this->generateOccurrences->handle($routine);

        return $routine->refresh();
    }
}
