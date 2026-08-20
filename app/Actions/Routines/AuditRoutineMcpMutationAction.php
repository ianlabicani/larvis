<?php

namespace App\Actions\Routines;

use App\Models\Routine;
use App\Models\RoutineMcpAudit;
use App\Models\RoutineOccurrence;
use App\Models\User;
use Illuminate\Support\Str;

class AuditRoutineMcpMutationAction
{
    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $result
     */
    public function handle(User $user, Routine $routine, ?RoutineOccurrence $occurrence, string $toolName, array $input, array $result, ?string $correlationId): RoutineMcpAudit
    {
        return RoutineMcpAudit::query()->create([
            'user_id' => $user->id,
            'routine_id' => $routine->id,
            'routine_occurrence_id' => $occurrence?->id,
            'tool_name' => $toolName,
            'input' => $input,
            'result' => $result,
            'correlation_id' => Str::isUuid((string) $correlationId) ? $correlationId : Str::uuid()->toString(),
        ]);
    }
}
