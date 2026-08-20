<?php

namespace App\Actions\Tasks;

use App\Data\TaskSummary;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GetTaskSummaryAction
{
    /**
     * Calculate summary counts for the actor's tasks.
     */
    public function handle(User $user): TaskSummary
    {
        Gate::forUser($user)->authorize('viewAny', Task::class);

        $openStatuses = [TaskStatus::Todo, TaskStatus::InProgress];
        $tasks = Task::query()->whereBelongsTo($user);

        return new TaskSummary(
            open: (clone $tasks)->whereIn('status', $openStatuses)->count(),
            overdue: (clone $tasks)
                ->whereIn('status', $openStatuses)
                ->where('due_at', '<', now())
                ->count(),
            dueSoon: (clone $tasks)
                ->whereIn('status', $openStatuses)
                ->whereBetween('due_at', [now(), now()->addDays(7)])
                ->count(),
            completed: (clone $tasks)->where('status', TaskStatus::Done)->count(),
        );
    }
}
