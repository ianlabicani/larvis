<?php

namespace App\Actions\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class ListTasksAction
{
    /**
     * List the actor's tasks using the optional status and priority filters.
     *
     * @return Collection<int, Task>
     */
    public function handle(User $user, ?TaskStatus $status = null, ?TaskPriority $priority = null): Collection
    {
        Gate::forUser($user)->authorize('viewAny', Task::class);

        return Task::query()
            ->whereBelongsTo($user)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($priority, fn ($query) => $query->where('priority', $priority))
            ->latest()
            ->get();
    }
}
