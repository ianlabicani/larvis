<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CreateTaskAction
{
    /**
     * Create a task for the actor.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes): Task
    {
        $this->authorize($user);

        return $user->tasks()->create([
            ...$attributes,
            'status' => TaskStatus::Todo,
            'completed_at' => null,
        ]);
    }

    /**
     * Authorize task creation for the actor.
     */
    public function authorize(User $user): void
    {
        Gate::forUser($user)->authorize('create', Task::class);
    }
}
