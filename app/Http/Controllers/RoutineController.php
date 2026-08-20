<?php

namespace App\Http\Controllers;

use App\Actions\Routines\CompleteRoutineOccurrenceAction;
use App\Actions\Routines\CreateRoutineAction;
use App\Actions\Routines\ListRoutinesAction;
use App\Actions\Routines\ListTodayRoutineOccurrencesAction;
use App\Actions\Routines\PauseRoutineAction;
use App\Actions\Routines\ResumeRoutineAction;
use App\Actions\Routines\UpdateRoutineAction;
use App\Data\RoutineData;
use App\Data\RoutineOccurrenceData;
use App\Enums\RoutineFrequency;
use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Routine;
use App\Models\RoutineOccurrence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoutineController extends Controller
{
    public function index(Request $request, ListRoutinesAction $listRoutines, ListTodayRoutineOccurrencesAction $listToday): Response
    {
        $today = $listToday->handle($request->user());

        return Inertia::render('routines/index', [
            'routines' => $listRoutines->handle($request->user())->map(fn (Routine $routine) => RoutineData::fromModel($routine)->toArray())->all(),
            'todayOccurrences' => $today->map(fn (RoutineOccurrence $occurrence) => [
                ...RoutineOccurrenceData::fromModel($occurrence)->toArray(),
                'routine' => RoutineData::fromModel($occurrence->routine)->toArray(),
            ])->all(),
            'frequencies' => array_map(fn (RoutineFrequency $frequency) => $frequency->value, RoutineFrequency::cases()),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function store(StoreRoutineRequest $request, CreateRoutineAction $createRoutine): RedirectResponse
    {
        $createRoutine->handle($request->user(), $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Routine created.')]);

        return to_route('routines.index');
    }

    public function update(UpdateRoutineRequest $request, Routine $routine, UpdateRoutineAction $updateRoutine): RedirectResponse
    {
        $updateRoutine->handle($request->user(), $routine, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Routine updated.')]);

        return to_route('routines.index');
    }

    public function pause(Request $request, Routine $routine, PauseRoutineAction $pauseRoutine): RedirectResponse
    {
        $pauseRoutine->handle($request->user(), $routine);

        return to_route('routines.index');
    }

    public function resume(Request $request, Routine $routine, ResumeRoutineAction $resumeRoutine): RedirectResponse
    {
        $resumeRoutine->handle($request->user(), $routine);

        return to_route('routines.index');
    }

    public function complete(Request $request, RoutineOccurrence $occurrence, CompleteRoutineOccurrenceAction $completeOccurrence): RedirectResponse
    {
        $completeOccurrence->handle($request->user(), $occurrence);
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Routine completed for today.')]);

        return back();
    }
}
