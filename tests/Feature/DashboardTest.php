<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->seed();
    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
