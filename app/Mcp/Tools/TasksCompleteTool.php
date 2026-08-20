<?php

namespace App\Mcp\Tools;

use App\Actions\Tasks\AuditTaskMcpMutationAction;
use App\Actions\Tasks\CompleteTaskAction;
use App\Data\TaskData;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Support\LarvisMcpOwner;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('tasks-complete')]
#[Description('Mark one task owned by the configured Larvis owner as done.')]
#[IsIdempotent]
class TasksCompleteTool extends Tool
{
    /**
     * Complete and audit an owner task.
     */
    public function handle(Request $request, LarvisMcpOwner $owner, CompleteTaskAction $completeTask, AuditTaskMcpMutationAction $auditTaskMcpMutation): Response|ResponseFactory
    {
        $user = $owner->resolve();

        if ($user === null) {
            return Response::error('The configured Larvis owner is unavailable. Run the database seeder before using task tools.');
        }

        $validated = $request->validate([
            'task_id' => ['required', 'integer', 'min:1'],
            'user_id' => ['prohibited'],
        ]);

        $task = $completeTask->handleById($user, $validated['task_id']);
        $result = ['task' => TaskData::fromModel($task)->toArray()];

        $auditTaskMcpMutation->handle($user, $task, 'tasks-complete', $validated, $result, $request->sessionId());

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
            'task_id' => $schema->integer()->required()->min(1)->description('The task ID to complete.'),
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
