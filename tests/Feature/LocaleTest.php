<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('serves the default locale (pl) translations by default', function () {
    $expected = json_decode(file_get_contents(lang_path('pl.json')), true);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('locale', 'pl')
        ->where('translations', $expected)
    );
});

it('switches the guest locale via session and reflects it on the next request', function () {
    $expected = json_decode(file_get_contents(lang_path('de.json')), true);

    $this->post('/jezyk', ['locale' => 'de'])->assertRedirect();

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('locale', 'de')
        ->where('translations', $expected)
    );
});

it('persists a logged-in user\'s locale choice to their account', function () {
    $user = User::factory()->create(['learner_locale' => 'pl']);

    $this->actingAs($user)->post('/jezyk', ['locale' => 'sk'])->assertRedirect();

    expect($user->fresh()->learner_locale)->toBe('sk');

    $this->actingAs($user)->get('/')->assertInertia(fn (Assert $page) => $page->where('locale', 'sk'));
});

it('rejects an unsupported locale', function () {
    $this->post('/jezyk', ['locale' => 'fr'])->assertSessionHasErrors('locale');
});
