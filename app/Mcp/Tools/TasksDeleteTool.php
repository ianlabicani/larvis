<?php

namespace App\Mcp\Tools;

use App\Actions\Tasks\AuditTaskMcpMutationAction;
use App\Actions\Tasks\DeleteTaskAction;
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

#[Name('tasks-delete')]
#[Description('Soft delete one task owned by the configured Larvis owner. Invoke only after the user explicitly confirms deletion.')]
#[IsIdempotent]
class TasksDeleteTool extends Tool
{
    /**
     * Soft delete and audit a configured owner task.
     */
    public function handle(Request $request, LarvisMcpOwner $owner, DeleteTaskAction $deleteTask, AuditTaskMcpMutationAction $auditTaskMcpMutation): Response|ResponseFactory
    {
        $user = $owner->resolve();

        if ($user === null) {
            return Response::error('The configured Larvis owner is unavailable. Run the database seeder before using task tools.');
        }

        $validated = $request->validate([
            'task_id' => ['required', 'integer', 'min:1'],
            'user_id' => ['prohibited'],
        ]);

        $deletion = $deleteTask->handleById($user, $validated['task_id']);
        $result = $deletion->toArray();

        if (! $deletion->alreadyDeleted) {
            $auditTaskMcpMutation->handle($user, $deletion->task, 'tasks-delete', $validated, $result, $request->sessionId());
        }

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
            'task' => $schema->object([
                'id' => $schema->integer()->required(),
                'title' => $schema->string()->required(),
                'deleted_at' => $schema->string()->required(),
            ])->withoutAdditionalProperties()->required(),
            'already_deleted' => $schema->boolean()->required(),
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
            'task_id' => $schema->integer()->required()->min(1)->description('The task ID to soft delete after explicit user confirmation.'),
        ];
    }
}
