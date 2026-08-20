<?php

use App\Actions\Routines\GenerateRoutineOccurrencesAction;
use App\Enums\RoutineOccurrenceStatus;
use App\Mcp\Servers\LarvisServer;
use App\Mcp\Tools\RoutinesCompleteTool;
use App\Mcp\Tools\RoutinesCreateTool;
use App\Mcp\Tools\RoutinesListTool;
use App\Models\Routine;
use App\Models\RoutineMcpAudit;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
    config()->set('larvis.mcp_owner_email', $this->owner->email);
    CarbonImmutable::setTestNow('2026-08-20 12:00:00 UTC');
});

afterEach(fn () => CarbonImmutable::setTestNow());

test('routine MCP tools create list and complete configured owner routines with audits', function (): void {
    LarvisServer::tool(RoutinesCreateTool::class, [
        'title' => 'Do 20 push-ups',
        'frequency' => 'daily',
        'scheduled_time' => '06:00',
        'timezone' => 'Asia/Manila',
        'starts_on' => '2026-08-20',
    ])->assertOk()->assertStructuredContent(fn ($json) => $json
        ->where('routine.title', 'Do 20 push-ups')
        ->where('routine.status', 'active')
        ->where('routine.timezone', 'Asia/Manila')
        ->etc());

    $routine = Routine::query()->firstOrFail();
    $occurrence = $routine->occurrences()->firstOrFail();

    LarvisServer::tool(RoutinesListTool::class)
        ->assertOk()->assertSee('Do 20 push-ups');

    LarvisServer::tool(RoutinesCompleteTool::class, ['occurrence_id' => $occurrence->id])
        ->assertOk()->assertStructuredContent(fn ($json) => $json
        ->where('occurrence.status', 'completed')
        ->where('already_completed', false)
        ->etc());

    expect($occurrence->refresh()->status)->toBe(RoutineOccurrenceStatus::Completed);
    expect(RoutineMcpAudit::query()->pluck('tool_name')->all())->toBe(['routines-create', 'routines-complete']);

    LarvisServer::tool(RoutinesCompleteTool::class, ['occurrence_id' => $occurrence->id])
        ->assertOk()->assertStructuredContent(fn ($json) => $json->where('already_completed', true)->etc());

    expect(RoutineMcpAudit::query()->where('tool_name', 'routines-complete')->count())->toBe(1);
});

test('routine MCP tools reject arbitrary owners cron and cross owner occurrences', function (): void {
    $otherRoutine = Routine::factory()->for(User::factory())->create(['starts_on' => '2026-08-20']);
    $occurrence = app(GenerateRoutineOccurrencesAction::class)->handle($otherRoutine)->firstOrFail();

    LarvisServer::tool(RoutinesCreateTool::class, [
        'title' => 'Unsafe',
        'scheduled_time' => '06:00',
        'timezone' => 'Asia/Manila',
        'starts_on' => '2026-08-20',
        'user_id' => 999,
        'cron' => '* * * * *',
    ])->assertHasErrors();

    LarvisServer::tool(RoutinesCompleteTool::class, ['occurrence_id' => $occurrence->id])
        ->assertHasErrors();
});

test('routine MCP tools fail safely when the configured owner is unavailable', function (): void {
    config()->set('larvis.mcp_owner_email', 'missing@example.com');

    LarvisServer::tool(RoutinesListTool::class)->assertHasErrors();
    LarvisServer::tool(RoutinesCreateTool::class)->assertHasErrors();
    LarvisServer::tool(RoutinesCompleteTool::class)->assertHasErrors();
});
