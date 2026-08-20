<?php

use App\Actions\Tasks\CompleteTaskAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
});

test('the owner can create, update, complete, reopen, and delete a task', function (): void {
    $this->actingAs($this->owner)
        ->post(route('tasks.store'), [
            'title' => 'Pay electricity bill',
            'description' => 'Pay before Friday',
            'priority' => TaskPriority::High->value,
            'due_at' => now()->addDays(2)->toDateTimeString(),
        ])
        ->assertRedirect(route('tasks.index'));

    $task = Task::query()->where('title', 'Pay electricity bill')->firstOrFail();

    $this->assertModelExists($task);
    expect($task)
        ->user_id->toBe($this->owner->id)
        ->status->toBe(TaskStatus::Todo)
        ->priority->toBe(TaskPriority::High);

    $this->actingAs($this->owner)
        ->patch(route('tasks.update', $task), [
            'title' => 'Pay electricity bill online',
            'description' => null,
            'status' => TaskStatus::Done->value,
            'priority' => TaskPriority::Urgent->value,
            'due_at' => null,
        ])
        ->assertRedirect(route('tasks.index'));

    expect($task->refresh())
        ->title->toBe('Pay electricity bill online')
        ->status->toBe(TaskStatus::Done)
        ->completed_at->not->toBeNull();

    $this->actingAs($this->owner)
        ->patch(route('tasks.reopen', $task))
        ->assertRedirect(route('tasks.index'));

    expect($task->refresh())
        ->status->toBe(TaskStatus::Todo)
        ->completed_at->toBeNull();

    $this->actingAs($this->owner)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'));

    $this->assertModelMissing($task);
});

test('task forms validate required fields and enum values', function (): void {
    $this->actingAs($this->owner)
        ->post(route('tasks.store'), [
            'title' => '',
            'priority' => 'critical',
        ])
        ->assertSessionHasErrors(['title', 'priority']);
});

test('the task index filters the owner tasks by status and priority', function (): void {
    $matchingTask = Task::factory()->for($this->owner)->create([
        'title' => 'Urgent work',
        'status' => TaskStatus::InProgress,
        'priority' => TaskPriority::Urgent,
    ]);
    Task::factory()->for($this->owner)->create([
        'title' => 'Later work',
        'status' => TaskStatus::Todo,
        'priority' => TaskPriority::Low,
    ]);

    $this->actingAs($this->owner)
        ->get(route('tasks.index', [
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::Urgent->value,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tasks/index')
            ->has('tasks', 1)
            ->where('tasks.0.id', $matchingTask->id)
            ->where('filters.status', TaskStatus::InProgress->value)
            ->where('filters.priority', TaskPriority::Urgent->value));
});

test('a user with task permissions cannot access another owner task', function (): void {
    $task = Task::factory()->for($this->owner)->create();
    $otherUser = User::factory()->create();
    $otherUser->assignRole('Larvis Owner');

    $this->actingAs($otherUser)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('tasks', 0));

    $this->actingAs($otherUser)
        ->patch(route('tasks.complete', $task))
        ->assertForbidden();

    expect(fn () => app(CompleteTaskAction::class)->handle($otherUser, $task))
        ->toThrow(AuthorizationException::class);
});

test('the dashboard presents owner scoped task counts', function (): void {
    Task::factory()->for($this->owner)->create([
        'status' => TaskStatus::Todo,
        'due_at' => now()->subDay(),
    ]);
    Task::factory()->for($this->owner)->create([
        'status' => TaskStatus::InProgress,
        'due_at' => now()->addDays(2),
    ]);
    Task::factory()->for($this->owner)->create([
        'status' => TaskStatus::Done,
        'completed_at' => now(),
    ]);

    $this->actingAs($this->owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('taskSummary.open', 2)
            ->where('taskSummary.overdue', 1)
            ->where('taskSummary.due_soon', 1)
            ->where('taskSummary.completed', 1));
});
