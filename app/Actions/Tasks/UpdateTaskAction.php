<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateTaskAction
{
    /**
     * Update a task after authorizing the actor.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, Task $task, array $attributes): Task
    {
        $this->authorize($user, $task);

        $status = TaskStatus::from($attributes['status']);

        $completedAt = $status === TaskStatus::Done
            ? ($task->completed_at ?? now())
            : null;

        $task->forceFill([
            ...$attributes,
            'completed_at' => $completedAt,
        ])->save();

        return $task->refresh();
    }

    /**
     * Authorize task updates for the actor.
     */
    public function authorize(User $user, Task $task): void
    {
        Gate::forUser($user)->authorize('update', $task);
    }
}
