<?php

use App\Models\User;

it('registers a student with just nick and pin', function () {
    $response = $this->post('/rejestracja', [
        'nick' => 'malakasia',
        'pin' => '1234',
        'pin_confirmation' => '1234',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticated();

    $user = User::where('nick', 'malakasia')->first();
    expect($user)->not->toBeNull()
        ->and($user->email)->toBeNull()
        ->and($user->is_adult)->toBeFalse();
});

it('rejects a duplicate nick', function () {
    User::factory()->create(['nick' => 'ala']);

    $response = $this->post('/rejestracja', [
        'nick' => 'ala',
        'pin' => '1234',
        'pin_confirmation' => '1234',
    ]);

    $response->assertSessionHasErrors('nick');
    $this->assertGuest();
});

it('rejects a blocked nick', function () {
    $response = $this->post('/rejestracja', [
        'nick' => 'admin',
        'pin' => '1234',
        'pin_confirmation' => '1234',
    ]);

    $response->assertSessionHasErrors('nick');
    $this->assertGuest();
});

it('rejects a pin confirmation mismatch', function () {
    $response = $this->post('/rejestracja', [
        'nick' => 'testowy',
        'pin' => '1234',
        'pin_confirmation' => '9999',
    ]);

    $response->assertSessionHasErrors('pin');
    $this->assertGuest();
});

it('rejects a too-short pin', function () {
    $response = $this->post('/rejestracja', [
        'nick' => 'testowy',
        'pin' => '12',
        'pin_confirmation' => '12',
    ]);

    $response->assertSessionHasErrors('pin');
});

it('registers an adult with email and password', function () {
    $response = $this->post('/rejestracja', [
        'nick' => 'doroslak',
        'pin' => '4321',
        'pin_confirmation' => '4321',
        'is_adult' => true,
        'email' => 'doroslak@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/');

    $user = User::where('nick', 'doroslak')->first();
    expect($user->is_adult)->toBeTrue()
        ->and($user->email)->toBe('doroslak@example.com');
});

it('imports guest points once at registration, capped at 500', function () {
    $this->post('/rejestracja', [
        'nick' => 'bogaty',
        'pin' => '1234',
        'pin_confirmation' => '1234',
        'guest_points' => 999999,
    ]);

    $user = User::where('nick', 'bogaty')->first();
    expect($user->attempts()->sum('points'))->toBe(500);
});
