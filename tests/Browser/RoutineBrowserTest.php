<?php

use App\Models\User;

beforeEach(function (): void {
    $this->seed();
    $this->owner = User::query()->where('email', 'test@example.com')->firstOrFail();
});

test('the owner can create pause resume and complete a routine in place', function (): void {
    $this->actingAs($this->owner);

    $page = visit(route('routines.index'))
        ->withTimezone('Asia/Manila');

    $page->assertSee('Routines')
        ->click('New routine')
        ->fill('Title', 'Do 20 push-ups')
        ->click('Create routine')
        ->assertSee('Do 20 push-ups')
        ->click('Pause')
        ->assertSee('Paused')
        ->click('Resume')
        ->assertSee('Active')
        ->click('Complete')
        ->assertSee('Completed')
        ->assertNoJavaScriptErrors();
});

test('the routines page remains usable on mobile', function (): void {
    $this->actingAs($this->owner);

    $page = visit(route('routines.index'))->on()->iPhone15Pro();

    $page->assertSee('Routines')
        ->click('New routine')
        ->assertSee('Create routine')
        ->assertNoJavaScriptErrors();

    expect($page->script('() => document.documentElement.scrollWidth > window.innerWidth'))->toBeFalse();
});
