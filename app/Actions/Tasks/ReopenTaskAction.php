<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ReopenTaskAction
{
    /**
     * Reopen a task as a to-do item.
     */
    public function handle(User $user, Task $task): Task
    {
        Gate::forUser($user)->authorize('update', $task);

        $task->forceFill([
            'status' => TaskStatus::Todo,
            'completed_at' => null,
        ])->save();

        return $task->refresh();
    }
}
