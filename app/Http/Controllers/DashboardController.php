<?php

namespace App\Http\Controllers;

use App\Actions\Routines\ListTodayRoutineOccurrencesAction;
use App\Actions\Tasks\GetTaskSummaryAction;
use App\Data\RoutineOccurrenceData;
use App\Models\RoutineOccurrence;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the task summary for the authenticated user.
     */
    public function __invoke(Request $request, GetTaskSummaryAction $getTaskSummary, ListTodayRoutineOccurrencesAction $listTodayRoutines): Response
    {
        return Inertia::render('dashboard', [
            'taskSummary' => $getTaskSummary->handle($request->user())->toArray(),
            'todayRoutines' => $listTodayRoutines->handle($request->user())->map(fn (RoutineOccurrence $occurrence): array => [
                ...RoutineOccurrenceData::fromModel($occurrence)->toArray(),
                'title' => $occurrence->routine->title,
            ])->all(),
        ]);
    }
}
