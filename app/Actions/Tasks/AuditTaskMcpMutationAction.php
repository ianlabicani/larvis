<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\TaskMcpAudit;
use App\Models\User;
use Illuminate\Support\Str;

class AuditTaskMcpMutationAction
{
    /**
     * Record a mutating MCP task operation.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $result
     */
    public function handle(User $user, Task $task, string $toolName, array $input, array $result, ?string $correlationId): TaskMcpAudit
    {
        return TaskMcpAudit::query()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'tool_name' => $toolName,
            'input' => $input,
            'result' => $result,
            'correlation_id' => Str::isUuid((string) $correlationId) ? $correlationId : Str::uuid()->toString(),
        ]);
    }
}
