<?php

namespace App\Actions\Routines;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateRoutineAction
{
    public function __construct(private GenerateRoutineOccurrencesAction $generateOccurrences) {}

    /** @param array<string, mixed> $attributes */
    public function handle(User $user, Routine $routine, array $attributes): Routine
    {
        Gate::forUser($user)->authorize('update', $routine);
        $this->generateOccurrences->handle($routine);

        $today = now($attributes['timezone'])->startOfDay();
        $routine->forceFill([...$attributes, 'generation_cursor' => $today->toDateString()])->save();

        return $routine->refresh();
    }
}
