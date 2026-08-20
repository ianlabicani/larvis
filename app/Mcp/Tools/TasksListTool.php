<?php

namespace App\Mcp\Tools;

use App\Actions\Tasks\ListTasksAction;
use App\Data\TaskData;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Support\LarvisMcpOwner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('tasks-list')]
#[Description('List the configured Larvis owner\'s tasks. Optionally filter by task status or priority.')]
#[IsReadOnly]
class TasksListTool extends Tool
{
    /**
     * List the configured owner's tasks.
     */
    public function handle(Request $request, LarvisMcpOwner $owner, ListTasksAction $listTasks): Response|ResponseFactory
    {
        $user = $owner->resolve();

        if ($user === null) {
            return Response::error('The configured Larvis owner is unavailable. Run the database seeder before using task tools.');
        }

        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'user_id' => ['prohibited'],
        ]);

        $status = isset($validated['status']) ? TaskStatus::from($validated['status']) : null;
        $priority = isset($validated['priority']) ? TaskPriority::from($validated['priority']) : null;

        return Response::structured([
            'tasks' => $listTasks->handle($user, $status, $priority)
                ->map(fn (Task $task): array => TaskData::fromModel($task)->toArray())
                ->all(),
        ]);
    }

    /**
     * Get the tool's output schema.
     *
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'tasks' => $schema->array()->items($this->taskSchema($schema))->required(),
        ];
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->enum(TaskStatus::class)->nullable()->description('Filter by status.'),
            'priority' => $schema->string()->enum(TaskPriority::class)->nullable()->description('Filter by priority.'),
        ];
    }

    private function taskSchema(JsonSchema $schema): Type
    {
        return $schema->object([
            'id' => $schema->integer()->required(),
            'title' => $schema->string()->required(),
            'description' => $schema->string()->nullable(),
            'status' => $schema->string()->enum(TaskStatus::class)->required(),
            'priority' => $schema->string()->enum(TaskPriority::class)->required(),
            'due_at' => $schema->string()->nullable(),
            'completed_at' => $schema->string()->nullable(),
            'is_overdue' => $schema->boolean()->required(),
            'is_due_soon' => $schema->boolean()->required(),
            'created_at' => $schema->string()->required(),
            'updated_at' => $schema->string()->required(),
        ])->withoutAdditionalProperties();
    }
}
