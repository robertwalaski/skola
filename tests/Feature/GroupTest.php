<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('lets a logged-in user create a group and become a teacher', function () {
    $user = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($user)->post('/nauczyciel/klasy', ['name' => 'Klasa 4B']);

    $response->assertRedirect(route('teacher.dashboard'));
    $user->refresh();
    expect($user->role)->toBe('teacher');

    $group = Group::where('name', 'Klasa 4B')->first();
    expect($group)->not->toBeNull()
        ->and($group->owner_id)->toBe($user->id)
        ->and(strlen($group->join_code))->toBe(6);
});

it('lets a student join a group by code', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $group = Group::create(['name' => 'Klasa 4B', 'join_code' => Group::generateJoinCode(), 'owner_id' => $teacher->id]);
    $student = User::factory()->create();

    $response = $this->actingAs($student)->post('/dolacz', ['join_code' => strtolower($group->join_code)]);

    $response->assertRedirect(route('join.show'));
    expect($student->currentGroup()?->id)->toBe($group->id);
});

it('rejects an unknown join code', function () {
    $student = User::factory()->create();

    $response = $this->actingAs($student)->post('/dolacz', ['join_code' => 'NOSUCH1']);

    $response->assertSessionHasErrors('join_code');
});

it('replaces the previous group when joining a new one', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $groupA = Group::create(['name' => 'A', 'join_code' => Group::generateJoinCode(), 'owner_id' => $teacher->id]);
    $groupB = Group::create(['name' => 'B', 'join_code' => Group::generateJoinCode(), 'owner_id' => $teacher->id]);
    $student = User::factory()->create();
    $student->groups()->attach($groupA->id);

    $this->actingAs($student)->post('/dolacz', ['join_code' => $groupB->join_code]);

    expect($student->groups()->count())->toBe(1)
        ->and($student->currentGroup()?->id)->toBe($groupB->id);
});

it('lets a teacher reset a student pin in their own group', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $group = Group::create(['name' => 'A', 'join_code' => Group::generateJoinCode(), 'owner_id' => $teacher->id]);
    $student = User::factory()->create(['pin_hash' => '1234']);
    $student->groups()->attach($group->id);

    $response = $this->actingAs($teacher)->post("/nauczyciel/klasy/{$group->id}/uczniowie/{$student->id}/reset-pin");

    $response->assertRedirect();
    $student->refresh();
    expect(Hash::check('1234', $student->pin_hash))->toBeFalse();
});

it('forbids resetting a pin in a group the teacher does not own', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $group = Group::create(['name' => 'A', 'join_code' => Group::generateJoinCode(), 'owner_id' => $otherTeacher->id]);
    $student = User::factory()->create();
    $student->groups()->attach($group->id);

    $this->actingAs($teacher)
        ->post("/nauczyciel/klasy/{$group->id}/uczniowie/{$student->id}/reset-pin")
        ->assertForbidden();
});
