<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class ListCalendarTasksAction
{
    /**
     * List the actor's due-dated tasks inside the requested calendar range.
     *
     * @return Collection<int, Task>
     */
    public function handle(User $user, CarbonInterface $start, CarbonInterface $end): Collection
    {
        $this->authorize($user);

        return Task::query()
            ->select(['id', 'user_id', 'title', 'description', 'status', 'priority', 'due_at'])
            ->whereBelongsTo($user)
            ->whereNotNull('due_at')
            ->where('due_at', '>=', $start)
            ->where('due_at', '<', $end)
            ->orderBy('due_at')
            ->get();
    }

    /**
     * Authorize calendar access for the actor.
     */
    public function authorize(User $user): void
    {
        Gate::forUser($user)->authorize('viewAny', Task::class);
    }
}
