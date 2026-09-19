<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Carbon;

it('shows only nicks and points in the global ranking, ordered by points', function () {
    $alice = User::factory()->create(['nick' => 'alice']);
    $bob = User::factory()->create(['nick' => 'bob']);
    $alice->attempts()->create(['course' => 'math', 'section' => 'multiplication', 'exercise_key' => 'x', 'correct' => true, 'points' => 10]);
    $bob->attempts()->create(['course' => 'math', 'section' => 'multiplication', 'exercise_key' => 'x', 'correct' => true, 'points' => 50]);

    $response = $this->get('/ranking');

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Ranking')
        ->where('global', [
            ['nick' => 'bob', 'points' => 50],
            ['nick' => 'alice', 'points' => 10],
        ])
    );
});

it('only counts this week for the week period', function () {
    $user = User::factory()->create(['nick' => 'stary']);
    $attempt = $user->attempts()->create([
        'course' => 'math', 'section' => 'multiplication', 'exercise_key' => 'x',
        'correct' => true, 'points' => 100,
    ]);
    $attempt->forceFill(['created_at' => Carbon::now()->subWeeks(2)])->save();

    $response = $this->get('/ranking?period=week');

    $response->assertInertia(fn ($page) => $page
        ->where('period', 'week')
        ->where('global', [['nick' => 'stary', 'points' => 0]])
    );
});

it('shows the current group ranking for a logged-in student', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $group = Group::create(['name' => 'Klasa', 'join_code' => Group::generateJoinCode(), 'owner_id' => $teacher->id]);
    $student = User::factory()->create(['nick' => 'uczen']);
    $student->groups()->attach($group->id);
    $student->attempts()->create(['course' => 'english', 'section' => 'alphabet', 'exercise_key' => 'x', 'correct' => true, 'points' => 5]);

    $response = $this->actingAs($student)->get('/ranking');

    $response->assertInertia(fn ($page) => $page
        ->where('group.name', 'Klasa')
        ->where('group.entries', [['nick' => 'uczen', 'points' => 5]])
    );
});

it('shows no group section for a guest or student without a group', function () {
    $response = $this->get('/ranking');

    $response->assertInertia(fn ($page) => $page->where('group', null));
});
