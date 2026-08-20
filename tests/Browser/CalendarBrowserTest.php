<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
});

test('the owner can navigate the default calendar and edit a task in place', function (): void {
    $task = Task::factory()->for($this->owner)->create([
        'title' => 'Calendar browser task',
        'description' => 'Task details are editable in the calendar.',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::High,
        'due_at' => now()->addDay()->setTime(9, 0),
    ]);

    $this->actingAs($this->owner);

    $page = visit(route('tasks.index'))->withTimezone('UTC');

    $page->assertSee('Tasks')
        ->assertSee('Calendar')
        ->assertSee($task->title)
        ->click('Week')
        ->assertSee($task->title)
        ->click($task->title)
        ->assertSee('Edit task')
        ->assertValue('Title', $task->title)
        ->assertValue('Description', 'Task details are editable in the calendar.')
        ->fill('Title', 'Updated calendar task')
        ->click('Save changes')
        ->assertSee('Updated calendar task')
        ->assertNoJavaScriptErrors();
});

test('the owner can create a task from a calendar date on mobile without leaving tasks', function (): void {
    $selectedDate = now()->addDays(5)->toDateString();

    $this->actingAs($this->owner);

    $page = visit(route('tasks.index'))
        ->withTimezone('UTC')
        ->on()
        ->iPhone15Pro();

    $page->assertSee('Tasks')
        ->assertSee('Calendar')
        ->click(sprintf('[data-date="%s"]', $selectedDate))
        ->assertSee('New task')
        ->assertPathBeginsWith('/tasks')
        ->assertValue('Due date', $selectedDate.'T09:00')
        ->fill('Title', 'Mobile calendar task')
        ->click('Create task')
        ->click('List')
        ->assertSee('Mobile calendar task')
        ->assertNoJavaScriptErrors();
});
