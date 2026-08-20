<?php

use App\Actions\Routines\CompleteRoutineOccurrenceAction;
use App\Actions\Routines\GenerateRoutineOccurrencesAction;
use App\Actions\Routines\PauseRoutineAction;
use App\Actions\Routines\ResumeRoutineAction;
use App\Enums\RoutineOccurrenceStatus;
use App\Enums\RoutineStatus;
use App\Models\Routine;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Artisan;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
    CarbonImmutable::setTestNow('2026-08-20 12:00:00 UTC');
});

afterEach(fn () => CarbonImmutable::setTestNow());

test('the owner can create a daily routine and receives todays independent occurrence', function (): void {
    $this->actingAs($this->owner)->post(route('routines.store'), [
        'title' => 'Do 20 push-ups',
        'description' => 'Morning exercise',
        'frequency' => 'daily',
        'scheduled_time' => '06:00',
        'timezone' => 'Asia/Manila',
        'starts_on' => '2026-08-20',
        'ends_on' => null,
    ])->assertRedirect(route('routines.index'));

    $routine = Routine::query()->firstOrFail();
    $occurrence = $routine->occurrences()->firstOrFail();

    expect($routine)
        ->user_id->toBe($this->owner->id)
        ->status->toBe(RoutineStatus::Active)
        ->timezone->toBe('Asia/Manila');
    expect($occurrence)
        ->local_date->toDateString()->toBe('2026-08-20')
        ->scheduled_for->toISOString()->toBe('2026-08-19T22:00:00.000000Z')
        ->status->toBe(RoutineOccurrenceStatus::Pending);
});

test('generation is idempotent and preserves completed history while marking older pending occurrences missed', function (): void {
    $routine = Routine::factory()->for($this->owner)->create(['starts_on' => '2026-08-18', 'generation_cursor' => null]);

    app(GenerateRoutineOccurrencesAction::class)->handle($routine);
    app(GenerateRoutineOccurrencesAction::class)->handle($routine->refresh());

    expect($routine->occurrences()->count())->toBe(3);

    $first = $routine->occurrences()->whereDate('local_date', '2026-08-18')->firstOrFail();
    $today = $routine->occurrences()->whereDate('local_date', '2026-08-20')->firstOrFail();

    expect($first->status)->toBe(RoutineOccurrenceStatus::Missed);

    app(CompleteRoutineOccurrenceAction::class)->handle($this->owner, $today);
    expect($today->refresh()->status)->toBe(RoutineOccurrenceStatus::Completed);
    expect($routine->occurrences()->whereDate('local_date', '2026-08-21')->exists())->toBeFalse();
});

test('pausing and resuming skip paused dates without deleting history', function (): void {
    $routine = Routine::factory()->for($this->owner)->create(['starts_on' => '2026-08-20']);
    app(GenerateRoutineOccurrencesAction::class)->handle($routine);
    app(PauseRoutineAction::class)->handle($this->owner, $routine);

    CarbonImmutable::setTestNow('2026-08-23 12:00:00 UTC');
    Artisan::call('routines:generate-occurrences');
    expect($routine->occurrences()->count())->toBe(1);

    app(ResumeRoutineAction::class)->handle($this->owner, $routine->refresh());

    expect($routine->refresh()->status)->toBe(RoutineStatus::Active);
    expect($routine->occurrences()->pluck('local_date')->map->toDateString()->all())->toBe(['2026-08-20', '2026-08-23']);
});

test('routine operations require permission and ownership', function (): void {
    $other = User::factory()->create();
    $other->assignRole('Larvis Owner');
    $routine = Routine::factory()->for($this->owner)->create();

    expect(fn () => app(PauseRoutineAction::class)->handle($other, $routine))
        ->toThrow(AuthorizationException::class);

    $this->actingAs($other)->put(route('routines.update', $routine), [
        'title' => 'Stolen',
        'frequency' => 'daily',
        'scheduled_time' => '06:00',
        'timezone' => 'Asia/Manila',
        'starts_on' => '2026-08-20',
    ])->assertForbidden();
});

test('routine validation rejects unsafe schedules and invalid date ranges', function (): void {
    $this->actingAs($this->owner)->post(route('routines.store'), [
        'title' => 'Invalid',
        'frequency' => 'weekly',
        'scheduled_time' => '25:00',
        'timezone' => 'Not/AZone',
        'starts_on' => '2026-08-20',
        'ends_on' => '2026-08-19',
        'cron' => '* * * * *',
    ])->assertSessionHasErrors(['frequency', 'scheduled_time', 'timezone', 'ends_on']);
});

test('routine scheduling preserves wall time across daylight saving transitions', function (string $date, string $expectedUtc): void {
    CarbonImmutable::setTestNow($date.' 18:00:00 America/New_York');
    $routine = Routine::factory()->for($this->owner)->create([
        'timezone' => 'America/New_York',
        'scheduled_time' => str_contains($date, '03-08') ? '02:30:00' : '01:30:00',
        'starts_on' => $date,
    ]);

    $occurrence = app(GenerateRoutineOccurrencesAction::class)->handle($routine)->firstOrFail();

    expect($occurrence->scheduled_for->toISOString())->toBe($expectedUtc);
})->with([
    'spring gap rolls forward by the gap' => ['2026-03-08', '2026-03-08T07:30:00.000000Z'],
    'fall overlap uses the first occurrence' => ['2026-11-01', '2026-11-01T05:30:00.000000Z'],
]);

test('routines appear on their page dashboard and calendar without becoming tasks', function (): void {
    $routine = Routine::factory()->for($this->owner)->create(['title' => 'Do 20 push-ups', 'starts_on' => '2026-08-20']);
    app(GenerateRoutineOccurrencesAction::class)->handle($routine);

    $this->actingAs($this->owner)->get(route('routines.index'))
        ->assertOk()->assertInertia(fn (Assert $page) => $page->component('routines/index')->where('routines.0.title', 'Do 20 push-ups'));

    $this->actingAs($this->owner)->get(route('dashboard'))
        ->assertOk()->assertInertia(fn (Assert $page) => $page->where('todayRoutines.0.title', 'Do 20 push-ups'));

    $this->actingAs($this->owner)->getJson(route('calendar.events', [
        'start' => '2026-08-19T00:00:00Z',
        'end' => '2026-08-22T00:00:00Z',
    ]))->assertOk()->assertJsonFragment(['type' => 'routine', 'title' => 'Do 20 push-ups']);

    expect(Task::query()->count())->toBe(0);
});
