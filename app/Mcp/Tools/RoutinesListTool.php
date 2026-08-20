<?php

namespace App\Mcp\Tools;

use App\Actions\Routines\ListRoutinesAction;
use App\Data\RoutineData;
use App\Enums\RoutineStatus;
use App\Models\Routine;
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

#[Name('routines-list')]
#[Description('List daily routines owned by the configured Larvis owner, including occurrence history and next schedule.')]
#[IsReadOnly]
class RoutinesListTool extends Tool
{
    /** @return array<string, Type> */
    public function outputSchema(JsonSchema $schema): array
    {
        return ['routines' => $schema->array()->items($schema->object())->required()];
    }

    public function handle(Request $request, LarvisMcpOwner $owner, ListRoutinesAction $listRoutines): Response|ResponseFactory
    {
        $user = $owner->resolve();
        if ($user === null) {
            return Response::error('The configured Larvis owner is unavailable. Run the database seeder before using routine tools.');
        }

        $validated = $request->validate(['status' => ['nullable', Rule::enum(RoutineStatus::class)], 'user_id' => ['prohibited']]);
        $status = isset($validated['status']) ? RoutineStatus::from($validated['status']) : null;

        return Response::structured(['routines' => $listRoutines->handle($user, $status)->map(fn (Routine $routine) => RoutineData::fromModel($routine)->toArray())->all()]);
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return ['status' => $schema->string()->enum(RoutineStatus::class)->nullable()->description('Optional active or paused filter.')];
    }
}
