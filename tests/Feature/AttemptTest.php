<?php

use App\Models\User;

it('rejects an attempt from a guest', function () {
    $response = $this->post('/attempts', [
        'course' => 'math',
        'section' => 'multiplication',
        'exercise_key' => 'mul|2|3',
        'correct' => true,
    ]);

    $response->assertRedirect('/logowanie');
});

it('records a correct attempt and computes points server-side, ignoring any client-sent points', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/attempts', [
        'course' => 'math',
        'section' => 'multiplication',
        'exercise_key' => 'mul|2|3',
        'correct' => true,
        'points' => 999999, // must be ignored
    ]);

    $response->assertOk()->assertJson(['points' => 10, 'total' => 10]);
    expect($user->attempts()->sole()->points)->toBe(10);
});

it('awards zero points for a wrong attempt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/attempts', [
        'course' => 'english',
        'section' => 'alphabet',
        'exercise_key' => 'missing:C',
        'correct' => false,
    ]);

    $response->assertOk()->assertJson(['points' => 0, 'total' => 0]);
});

it('rejects an unknown section for the given course', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/attempts', [
        'course' => 'math',
        'section' => 'not-a-real-section',
        'exercise_key' => 'x',
        'correct' => true,
    ]);

    $response->assertStatus(422);
    expect($user->attempts()->count())->toBe(0);
});

it('rejects an unknown course', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/attempts', [
        'course' => 'history',
        'section' => 'anything',
        'exercise_key' => 'x',
        'correct' => true,
    ]);

    $response->assertStatus(422);
});
