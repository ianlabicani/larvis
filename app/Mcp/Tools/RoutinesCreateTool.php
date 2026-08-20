<?php

namespace App\Mcp\Tools;

use App\Actions\Routines\AuditRoutineMcpMutationAction;
use App\Actions\Routines\CreateRoutineAction;
use App\Data\RoutineData;
use App\Enums\RoutineFrequency;
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

#[Name('routines-create')]
#[Description('Create a safe daily routine for the configured Larvis owner using local time and an IANA timezone. Raw cron expressions are not accepted.')]
class RoutinesCreateTool extends Tool
{
    /** @return array<string, Type> */
    public function outputSchema(JsonSchema $schema): array
    {
        return ['routine' => $this->routineSchema($schema)->required()];
    }

    public function handle(Request $request, LarvisMcpOwner $owner, CreateRoutineAction $createRoutine, AuditRoutineMcpMutationAction $audit): Response|ResponseFactory
    {
        $user = $owner->resolve();
        if ($user === null) {
            return Response::error('The configured Larvis owner is unavailable. Run the database seeder before using routine tools.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'frequency' => ['nullable', Rule::enum(RoutineFrequency::class)],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'timezone:all'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'user_id' => ['prohibited'],
            'cron' => ['prohibited'],
        ]);
        $input = [...$validated, 'frequency' => $validated['frequency'] ?? RoutineFrequency::Daily->value];
        $routine = $createRoutine->handle($user, $input);
        $result = ['routine' => RoutineData::fromModel($routine)->toArray()];
        $audit->handle($user, $routine, null, 'routines-create', $input, $result, $request->sessionId());

        return Response::structured($result);
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required()->max(255),
            'description' => $schema->string()->nullable()->max(5000),
            'frequency' => $schema->string()->enum(RoutineFrequency::class)->nullable()->description('Daily is the only supported frequency.'),
            'scheduled_time' => $schema->string()->required()->description('Local time in HH:MM format.'),
            'timezone' => $schema->string()->required()->description('IANA timezone such as Asia/Manila.'),
            'starts_on' => $schema->string()->required()->format('date'),
            'ends_on' => $schema->string()->nullable()->format('date'),
        ];
    }

    private function routineSchema(JsonSchema $schema): Type
    {
        return $schema->object([
            'id' => $schema->integer()->required(), 'title' => $schema->string()->required(),
            'description' => $schema->string()->nullable(), 'status' => $schema->string()->required(),
            'frequency' => $schema->string()->required(), 'scheduled_time' => $schema->string()->required(),
            'timezone' => $schema->string()->required(), 'starts_on' => $schema->string()->required(),
            'ends_on' => $schema->string()->nullable(), 'next_occurrence_at' => $schema->string()->nullable(),
            'occurrences' => $schema->array()->items($schema->object()),
        ]);
    }
}
