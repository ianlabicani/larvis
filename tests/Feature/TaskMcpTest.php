<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Mcp\Servers\LarvisServer;
use App\Mcp\Tools\TasksCompleteTool;
use App\Mcp\Tools\TasksCreateTool;
use App\Mcp\Tools\TasksListTool;
use App\Models\Task;
use App\Models\TaskMcpAudit;
use App\Models\User;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Transport\FakeTransporter;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
    config()->set('larvis.mcp_owner_email', $this->owner->email);
});

test('the Larvis server exposes only the scoped task tools', function (): void {
    $server = new LarvisServer(new FakeTransporter);

    expect($server->createContext()->tools()->map(fn (Tool $tool): string => $tool->name())->all())
        ->toBe(['tasks-list', 'tasks-create', 'tasks-complete']);
});

test('the create tool creates an owner task with structured output and an audit record', function (): void {
    LarvisServer::tool(TasksCreateTool::class, [
        'title' => 'Pay electricity bill',
        'priority' => TaskPriority::High->value,
        'due_at' => now()->addDays(2)->toISOString(),
    ])
        ->assertName('tasks-create')
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('task.title', 'Pay electricity bill')
            ->where('task.status', TaskStatus::Todo->value)
            ->where('task.priority', TaskPriority::High->value)
            ->where('task.is_due_soon', true)
            ->whereType('task.id', 'integer'));

    $task = Task::query()->firstOrFail();
    $audit = TaskMcpAudit::query()->firstOrFail();

    expect($task->user_id)->toBe($this->owner->id);
    expect($audit)
        ->tool_name->toBe('tasks-create')
        ->task_id->toBe($task->id)
        ->input->toMatchArray(['title' => 'Pay electricity bill', 'priority' => TaskPriority::High->value]);
});

test('the list tool returns only the configured owner tasks', function (): void {
    Task::factory()->for($this->owner)->create(['title' => 'Owner task']);
    Task::factory()->for(User::factory())->create(['title' => 'Other user task']);

    LarvisServer::tool(TasksListTool::class)
        ->assertName('tasks-list')
        ->assertOk()
        ->assertSee('Owner task')
        ->assertDontSee('Other user task');
});

test('the complete tool updates the task and writes an audit record', function (): void {
    $task = Task::factory()->for($this->owner)->create([
        'status' => TaskStatus::Todo,
        'completed_at' => null,
    ]);

    LarvisServer::tool(TasksCompleteTool::class, ['task_id' => $task->id])
        ->assertName('tasks-complete')
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('task.id', $task->id)
            ->where('task.status', TaskStatus::Done->value)
            ->whereType('task.completed_at', 'string'));

    expect($task->refresh())
        ->status->toBe(TaskStatus::Done)
        ->completed_at->not->toBeNull();
    expect(TaskMcpAudit::query()->latest()->firstOrFail()->tool_name)->toBe('tasks-complete');
});

test('the task tools reject invalid input and arbitrary owner input', function (): void {
    LarvisServer::tool(TasksCreateTool::class, ['priority' => 'invalid'])
        ->assertHasErrors(['title']);

    LarvisServer::tool(TasksListTool::class, ['user_id' => 999])
        ->assertHasErrors(['user id']);
});
