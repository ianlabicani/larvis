<?php

namespace App\Actions\Routines;

use App\Enums\RoutineOccurrenceStatus;
use App\Models\RoutineOccurrence;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CompleteRoutineOccurrenceAction
{
    public function handle(User $user, RoutineOccurrence $occurrence): RoutineOccurrence
    {
        $occurrence->loadMissing('routine.user');
        Gate::forUser($user)->authorize('update', $occurrence->routine);

        if ($occurrence->status === RoutineOccurrenceStatus::Missed) {
            throw ValidationException::withMessages(['occurrence' => __('Missed occurrences cannot be completed.')]);
        }

        if ($occurrence->status === RoutineOccurrenceStatus::Completed) {
            return $occurrence;
        }

        $occurrence->forceFill(['status' => RoutineOccurrenceStatus::Completed, 'completed_at' => now()])->save();

        return $occurrence->refresh()->load('routine');
    }

    public function handleById(User $user, int $occurrenceId): RoutineOccurrence
    {
        $occurrence = RoutineOccurrence::query()->with('routine.user')->findOrFail($occurrenceId);

        if (! $user->is($occurrence->routine->user)) {
            throw new AuthorizationException;
        }

        return $this->handle($user, $occurrence);
    }
}
