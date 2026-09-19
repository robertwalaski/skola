<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the multiplication game', function () {
    $this->get('/matematyka/tabliczka')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Math/Multiplication'));
});

it('renders the alphabet game', function () {
    $this->get('/angielski/alfabet')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('English/Alphabet'));
});
