<?php

namespace App\Http\Controllers;

use App\Actions\Routines\ListCalendarRoutineEventsAction;
use App\Actions\Tasks\ListCalendarTasksAction;
use App\Data\CalendarEventData;
use App\Http\Requests\CalendarEventsRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class CalendarController extends Controller
{
    /**
     * Return the actor's due-dated tasks for a visible calendar range.
     */
    public function events(CalendarEventsRequest $request, ListCalendarTasksAction $listCalendarTasks, ListCalendarRoutineEventsAction $listRoutineEvents): JsonResponse
    {
        $tasks = $listCalendarTasks->handle($request->user(), $request->start(), $request->end())
            ->map(fn (Task $task): array => CalendarEventData::fromModel($task)->toArray())
            ->all();
        $routines = $listRoutineEvents->handle($request->user(), $request->start(), $request->end())
            ->map(fn ($event): array => $event->toArray())
            ->all();

        return response()->json(collect([...$tasks, ...$routines])->sortBy('start')->values()->all());
    }
}
