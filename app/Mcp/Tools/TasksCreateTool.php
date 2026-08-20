<?php

namespace App\Mcp\Tools;

use App\Actions\Tasks\AuditTaskMcpMutationAction;
use App\Actions\Tasks\CreateTaskAction;
use App\Data\TaskData;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
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

#[Name('tasks-create')]
#[Description('Create a new to-do task for the configured Larvis owner.')]
class TasksCreateTool extends Tool
{
    /**
     * Create and audit a task for the configured owner.
     */
    public function handle(Request $request, LarvisMcpOwner $owner, CreateTaskAction $createTask, AuditTaskMcpMutationAction $auditTaskMcpMutation): Response|ResponseFactory
    {
        $user = $owner->resolve();

        if ($user === null) {
            return Response::error('The configured Larvis owner is unavailable. Run the database seeder before using task tools.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'due_at' => ['nullable', 'date'],
            'user_id' => ['prohibited'],
        ]);

        $input = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? TaskPriority::Medium->value,
            'due_at' => $validated['due_at'] ?? null,
        ];
        $task = $createTask->handle($user, $input);
        $result = ['task' => TaskData::fromModel($task)->toArray()];

        $auditTaskMcpMutation->handle($user, $task, 'tasks-create', $input, $result, $request->sessionId());

        return Response::structured($result);
    }

    /**
     * Get the tool's output schema.
     *
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'task' => $this->taskSchema($schema)->required(),
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
            'title' => $schema->string()->required()->max(255)->description('A short task title.'),
            'description' => $schema->string()->nullable()->max(5000)->description('Optional task details.'),
            'priority' => $schema->string()->enum(TaskPriority::class)->nullable()->description('Optional priority; defaults to medium.'),
            'due_at' => $schema->string()->nullable()->format('date-time')->description('Optional ISO 8601 due date and time.'),
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
