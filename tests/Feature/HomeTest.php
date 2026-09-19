<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the home page with course translations', function () {
    $expected = json_decode(file_get_contents(lang_path('pl.json')), true);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('locale', 'pl')
            ->where('translations', $expected)
        );
});
