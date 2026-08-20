<?php

namespace App\Mcp\Tools;

use App\Actions\Routines\AuditRoutineMcpMutationAction;
use App\Actions\Routines\CompleteRoutineOccurrenceAction;
use App\Data\RoutineData;
use App\Data\RoutineOccurrenceData;
use App\Enums\RoutineOccurrenceStatus;
use App\Models\RoutineOccurrence;
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

#[Name('routines-complete')]
#[Description('Complete one pending routine occurrence owned by the configured Larvis owner.')]
#[IsIdempotent]
class RoutinesCompleteTool extends Tool
{
    /** @return array<string, Type> */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'routine' => $schema->object()->required(),
            'occurrence' => $schema->object([
                'id' => $schema->integer()->required(), 'local_date' => $schema->string()->required(),
                'scheduled_for' => $schema->string()->required(), 'status' => $schema->string()->required(),
                'completed_at' => $schema->string()->nullable(),
            ])->required(),
            'already_completed' => $schema->boolean()->required(),
        ];
    }

    public function handle(Request $request, LarvisMcpOwner $owner, CompleteRoutineOccurrenceAction $completeOccurrence, AuditRoutineMcpMutationAction $audit): Response|ResponseFactory
    {
        $user = $owner->resolve();
        if ($user === null) {
            return Response::error('The configured Larvis owner is unavailable. Run the database seeder before using routine tools.');
        }

        $validated = $request->validate(['occurrence_id' => ['required', 'integer', 'min:1'], 'user_id' => ['prohibited']]);
        $occurrenceId = (int) $validated['occurrence_id'];
        $occurrence = RoutineOccurrence::query()->findOrFail($occurrenceId);
        $occurrence->load('routine.occurrences');
        $alreadyCompleted = $occurrence->status === RoutineOccurrenceStatus::Completed;
        $occurrence = $completeOccurrence->handle($user, $occurrence);
        $result = [
            'routine' => RoutineData::fromModel($occurrence->routine)->toArray(),
            'occurrence' => RoutineOccurrenceData::fromModel($occurrence)->toArray(),
            'already_completed' => $alreadyCompleted,
        ];

        if (! $alreadyCompleted) {
            $audit->handle($user, $occurrence->routine, $occurrence, 'routines-complete', $validated, $result, $request->sessionId());
        }

        return Response::structured($result);
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return ['occurrence_id' => $schema->integer()->required()->min(1)->description('The pending routine occurrence ID to complete.')];
    }
}
