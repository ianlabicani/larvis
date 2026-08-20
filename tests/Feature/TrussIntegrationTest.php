<?php

use AlbertoArena\Truss\Facades\Truss;
use App\Models\User;
use Laravel\Mcp\Server\Registrar;

test('the dashboard is available in the local environment', function (): void {
    app()->detectEnvironment(fn (): string => 'local');
    config()->set('truss.enabled', true);

    $this->get('/truss')->assertOk();
});

test('the dashboard is unavailable outside the local environment by default', function (): void {
    expect(config('truss.enabled'))->toBeFalse();

    $this->get('/truss')->assertNotFound();
});

test('the Truss MCP server is disabled', function (): void {
    expect(config('truss.mcp.enabled'))->toBeFalse()
        ->and(app(Registrar::class)->getLocalServer('truss'))->toBeNull();
});

test('the structure export contains Larvis domain relationships without row data', function (): void {
    $user = User::factory()->create([
        'name' => 'Private Truss Row Value',
    ]);

    $schema = Truss::snapshot()->fresh()->toDbml();

    expect($schema)
        ->toContain('Table users {')
        ->toContain('Table tasks {')
        ->toContain('Table task_mcp_audits {')
        ->toContain('Table routines {')
        ->toContain('Table routine_occurrences {')
        ->toContain('Table routine_mcp_audits {')
        ->toContain('Ref: tasks.user_id > users.id')
        ->toContain('Ref: task_mcp_audits.task_id > tasks.id')
        ->toContain('Ref: routines.user_id > users.id')
        ->toContain('Ref: routine_occurrences.routine_id > routines.id')
        ->toContain('Ref: routine_mcp_audits.routine_id > routines.id')
        ->not->toContain($user->name);
});
