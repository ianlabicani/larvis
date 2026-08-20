<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::patch('tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');
    Route::resource('routines', RoutineController::class)->only(['index', 'store', 'update']);
    Route::patch('routines/{routine}/pause', [RoutineController::class, 'pause'])->name('routines.pause');
    Route::patch('routines/{routine}/resume', [RoutineController::class, 'resume'])->name('routines.resume');
    Route::patch('routine-occurrences/{occurrence}/complete', [RoutineController::class, 'complete'])->name('routine-occurrences.complete');
});

require __DIR__.'/settings.php';
