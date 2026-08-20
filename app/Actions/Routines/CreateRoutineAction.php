<?php

namespace App\Actions\Routines;

use App\Enums\RoutineFrequency;
use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CreateRoutineAction
{
    public function __construct(private GenerateRoutineOccurrencesAction $generateOccurrences) {}

    /** @param array<string, mixed> $attributes */
    public function handle(User $user, array $attributes): Routine
    {
        Gate::forUser($user)->authorize('create', Routine::class);

        $routine = $user->routines()->create([
            ...$attributes,
            'status' => RoutineStatus::Active,
            'frequency' => RoutineFrequency::Daily,
        ]);

        $this->generateOccurrences->handle($routine);

        return $routine->refresh()->load(['occurrences' => fn ($query) => $query->latest('local_date')]);
    }
}
