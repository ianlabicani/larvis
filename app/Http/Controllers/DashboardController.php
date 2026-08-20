<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\GetTaskSummaryAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the task summary for the authenticated user.
     */
    public function __invoke(Request $request, GetTaskSummaryAction $getTaskSummary): Response
    {
        return Inertia::render('dashboard', [
            'taskSummary' => $getTaskSummary->handle($request->user())->toArray(),
        ]);
    }
}
