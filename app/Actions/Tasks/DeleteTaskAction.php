<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DeleteTaskAction
{
    /**
     * Delete a task after authorizing the actor.
     */
    public function handle(User $user, Task $task): void
    {
        Gate::forUser($user)->authorize('delete', $task);

        $task->delete();
    }
}
