<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CompleteTaskAction
{
    /**
     * Mark a task as complete.
     */
    public function handle(User $user, Task $task): Task
    {
        Gate::forUser($user)->authorize('update', $task);

        $task->forceFill([
            'status' => TaskStatus::Done,
            'completed_at' => $task->completed_at ?? now(),
        ])->save();

        return $task->refresh();
    }

    /**
     * Find and complete a task for the actor.
     */
    public function handleById(User $user, int $taskId): Task
    {
        $task = Task::query()->findOrFail($taskId);

        return $this->handle($user, $task);
    }
}
