<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
});

test('dashboard, tasks, and settings share the same desktop page spacing', function (): void {
    $this->actingAs($this->owner);

    $expectedPadding = [
        'top' => '24px',
        'right' => '16px',
        'bottom' => '24px',
        'left' => '16px',
    ];
    $pageSpacing = <<<'JS'
        () => {
            const content = document.querySelector('[data-slot="page-content"]');
            const styles = window.getComputedStyle(content);

            return {
                top: styles.paddingTop,
                right: styles.paddingRight,
                bottom: styles.paddingBottom,
                left: styles.paddingLeft,
            };
        }
    JS;
    $hasHorizontalOverflow = '() => document.documentElement.scrollWidth > window.innerWidth';

    $dashboard = visit(route('dashboard'));

    expect($dashboard->script($pageSpacing))->toBe($expectedPadding)
        ->and($dashboard->script($hasHorizontalOverflow))->toBeFalse();

    $tasks = visit(route('tasks.index'));

    expect($tasks->script($pageSpacing))->toBe($expectedPadding)
        ->and($tasks->script($hasHorizontalOverflow))->toBeFalse();

    $settings = visit(route('profile.edit'));

    expect($settings->script($pageSpacing))->toBe($expectedPadding)
        ->and($settings->script($hasHorizontalOverflow))->toBeFalse();

    $dashboard->assertNoJavaScriptErrors();
    $tasks->assertNoJavaScriptErrors();
    $settings->assertNoJavaScriptErrors();
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
