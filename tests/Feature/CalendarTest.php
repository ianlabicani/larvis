<?php

use App\Actions\Tasks\ListCalendarTasksAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
});

test('the owner receives the configured application timezone on the tasks page', function (): void {
    $this->actingAs($this->owner)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tasks/index')
            ->where('timezone', config('app.timezone')));
});

test('calendar events include only due-dated owner tasks in the requested range', function (): void {
    $start = CarbonImmutable::parse('2026-08-01 00:00:00', config('app.timezone'));
    $end = $start->addDays(14);
    $matchingTask = Task::factory()->for($this->owner)->create([
        'title' => 'First calendar task',
        'description' => 'Visible in the calendar',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Urgent,
        'due_at' => $start->addDay()->setTime(9, 30),
    ]);
    $laterTask = Task::factory()->for($this->owner)->create([
        'due_at' => $start->addDays(3),
    ]);
    Task::factory()->for($this->owner)->create(['due_at' => null]);
    Task::factory()->for($this->owner)->create(['due_at' => $end]);

    $this->actingAs($this->owner)
        ->getJson(route('calendar.events', [
            'start' => $start->toISOString(),
            'end' => $end->toISOString(),
        ]))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('0.id', (string) $matchingTask->id)
        ->assertJsonPath('0.title', 'First calendar task')
        ->assertJsonPath('0.description', 'Visible in the calendar')
        ->assertJsonPath('0.start', $matchingTask->due_at?->toISOString())
        ->assertJsonPath('0.status', TaskStatus::InProgress->value)
        ->assertJsonPath('0.priority', TaskPriority::Urgent->value)
        ->assertJsonPath('1.id', (string) $laterTask->id);
});

test('calendar events remain owner scoped when another user has task permissions', function (): void {
    $otherUser = User::factory()->create();
    $otherUser->assignRole('Larvis Owner');
    $start = CarbonImmutable::parse('2026-08-01 00:00:00', config('app.timezone'));
    $end = $start->addWeek();
    $otherTask = Task::factory()->for($otherUser)->create(['due_at' => $start->addDay()]);
    Task::factory()->for($this->owner)->create(['due_at' => $start->addDays(2)]);

    $this->actingAs($otherUser)
        ->getJson(route('calendar.events', [
            'start' => $start->toISOString(),
            'end' => $end->toISOString(),
        ]))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', (string) $otherTask->id);
});

test('calendar ranges are validated before querying tasks', function (): void {
    $start = CarbonImmutable::parse('2026-08-01 00:00:00', config('app.timezone'));

    $this->actingAs($this->owner)
        ->getJson(route('calendar.events', [
            'start' => $start->toISOString(),
            'end' => $start->subSecond()->toISOString(),
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end');

    $this->actingAs($this->owner)
        ->getJson(route('calendar.events', [
            'start' => $start->toISOString(),
            'end' => $start->addDays(63)->toISOString(),
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end');
});

test('the calendar action authorizes the actor and orders due tasks', function (): void {
    $start = CarbonImmutable::parse('2026-08-01 00:00:00', config('app.timezone'));
    $laterTask = Task::factory()->for($this->owner)->create(['due_at' => $start->addDays(2)]);
    $earlierTask = Task::factory()->for($this->owner)->create(['due_at' => $start->addDay()]);

    $tasks = app(ListCalendarTasksAction::class)->handle($this->owner, $start, $start->addWeek());

    expect($tasks->modelKeys())->toBe([$earlierTask->id, $laterTask->id]);

    $unauthorizedUser = User::factory()->create();

    expect(fn () => app(ListCalendarTasksAction::class)->handle($unauthorizedUser, $start, $start->addWeek()))
        ->toThrow(AuthorizationException::class);
});

test('task create and edit pages are not exposed separately', function (): void {
    $this->actingAs($this->owner)
        ->get('/tasks/create')
        ->assertMethodNotAllowed();

    $task = Task::factory()->for($this->owner)->create();

    $this->actingAs($this->owner)
        ->get(route('tasks.index').'/'.$task->id.'/edit')
        ->assertNotFound();
});
