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

it('renders the irregular verbs game', function () {
    $this->get('/angielski/czasowniki-nieregularne')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('English/IrregularVerbs'));
});

it('renders the conditionals lesson', function () {
    $this->get('/angielski/conditionals')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('English/Conditionals'));
});

it('renders the wishes lesson', function () {
    $this->get('/angielski/wishes')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('English/Wishes'));
});
