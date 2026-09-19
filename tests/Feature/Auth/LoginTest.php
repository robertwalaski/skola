<?php

use App\Models\User;

it('logs in with the correct nick and pin', function () {
    User::factory()->create(['nick' => 'gracz', 'pin_hash' => '1234']);

    $response = $this->post('/logowanie', ['nick' => 'gracz', 'pin' => '1234']);

    $response->assertRedirect('/');
    $this->assertAuthenticated();
});

it('rejects the wrong pin', function () {
    User::factory()->create(['nick' => 'gracz', 'pin_hash' => '1234']);

    $response = $this->post('/logowanie', ['nick' => 'gracz', 'pin' => '0000']);

    $response->assertSessionHasErrors('nick');
    $this->assertGuest();
});

it('rejects a nonexistent nick', function () {
    $response = $this->post('/logowanie', ['nick' => 'nikt-taki', 'pin' => '1234']);

    $response->assertSessionHasErrors('nick');
    $this->assertGuest();
});

it('rate limits repeated failed logins', function () {
    User::factory()->create(['nick' => 'gracz', 'pin_hash' => '1234']);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/logowanie', ['nick' => 'gracz', 'pin' => '0000']);
    }

    // The 6th attempt should be throttled even though it hasn't tried yet
    // this request - the previous 5 already used up the window.
    $response = $this->post('/logowanie', ['nick' => 'gracz', 'pin' => '1234']);

    $response->assertSessionHasErrors('nick');
    $this->assertGuest();
});
