<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\CompleteTaskAction;
use App\Actions\Tasks\CreateTaskAction;
use App\Actions\Tasks\DeleteTaskAction;
use App\Actions\Tasks\ListTasksAction;
use App\Actions\Tasks\ReopenTaskAction;
use App\Actions\Tasks\UpdateTaskAction;
use App\Data\TaskData;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\ListTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Display the actor's filtered tasks.
     */
    public function index(ListTasksRequest $request, ListTasksAction $listTasks): Response
    {
        $filters = $request->validated();
        $status = isset($filters['status']) ? TaskStatus::from($filters['status']) : null;
        $priority = isset($filters['priority']) ? TaskPriority::from($filters['priority']) : null;

        return Inertia::render('tasks/index', [
            'tasks' => $listTasks->handle($request->user(), $status, $priority)
                ->map(fn (Task $task): array => TaskData::fromModel($task)->toArray())
                ->all(),
            'filters' => [
                'status' => $status?->value,
                'priority' => $priority?->value,
            ],
            ...$this->taskOptions(),
        ]);
    }

    /**
     * Show the task creation form.
     */
    public function create(Request $request, CreateTaskAction $createTask): Response
    {
        $createTask->authorize($request->user());

        return Inertia::render('tasks/create', $this->taskOptions());
    }

    /**
     * Store a new task.
     */
    public function store(StoreTaskRequest $request, CreateTaskAction $createTask): RedirectResponse
    {
        $createTask->handle($request->user(), $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task created.')]);

        return to_route('tasks.index');
    }

    /**
     * Show the task edit form.
     */
    public function edit(Task $task, Request $request, UpdateTaskAction $updateTask): Response
    {
        $updateTask->authorize($request->user(), $task);

        return Inertia::render('tasks/edit', [
            'task' => TaskData::fromModel($task)->toArray(),
            ...$this->taskOptions(),
        ]);
    }

    /**
     * Update an existing task.
     */
    public function update(UpdateTaskRequest $request, Task $task, UpdateTaskAction $updateTask): RedirectResponse
    {
        $updateTask->handle($request->user(), $task, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task updated.')]);

        return to_route('tasks.index');
    }

    /**
     * Complete a task.
     */
    public function complete(Task $task, Request $request, CompleteTaskAction $completeTask): RedirectResponse
    {
        $completeTask->handle($request->user(), $task);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task completed.')]);

        return to_route('tasks.index');
    }

    /**
     * Reopen a task.
     */
    public function reopen(Task $task, Request $request, ReopenTaskAction $reopenTask): RedirectResponse
    {
        $reopenTask->handle($request->user(), $task);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task reopened.')]);

        return to_route('tasks.index');
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task, Request $request, DeleteTaskAction $deleteTask): RedirectResponse
    {
        $deleteTask->handle($request->user(), $task);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task deleted.')]);

        return to_route('tasks.index');
    }

    /**
     * @return array{priorities: list<string>, statuses: list<string>}
     */
    private function taskOptions(): array
    {
        return [
            'statuses' => array_map(fn (TaskStatus $status): string => $status->value, TaskStatus::cases()),
            'priorities' => array_map(fn (TaskPriority $priority): string => $priority->value, TaskPriority::cases()),
        ];
    }
}
