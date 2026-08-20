<?php

namespace App\Actions\Tasks;

use App\Data\TaskDeletionData;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DeleteTaskAction
{
    /**
     * Delete a task after authorizing the actor.
     */
    public function handle(User $user, Task $task): TaskDeletionData
    {
        Gate::forUser($user)->authorize('delete', $task);

        if ($task->trashed()) {
            return new TaskDeletionData($task, true);
        }

        $task->delete();

        return new TaskDeletionData($task, false);
    }

    /**
     * Find and soft delete a task for the actor, including a prior deletion.
     */
    public function handleById(User $user, int $taskId): TaskDeletionData
    {
        $task = Task::withTrashed()->findOrFail($taskId);

        return $this->handle($user, $task);
    }
}
