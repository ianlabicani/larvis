<?php

namespace App\Http\Controllers;

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
    public function events(CalendarEventsRequest $request, ListCalendarTasksAction $listCalendarTasks): JsonResponse
    {
        return response()->json(
            $listCalendarTasks->handle($request->user(), $request->start(), $request->end())
                ->map(fn (Task $task): array => CalendarEventData::fromModel($task)->toArray())
                ->all(),
        );
    }
}
