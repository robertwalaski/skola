<?php

it('has well-formed irregular verb data', function () {
    $data = json_decode(file_get_contents(resource_path('content/english/irregular-verbs.json')), true);

    expect($data['verbs'])->not->toBeEmpty();

    foreach ($data['verbs'] as $verb) {
        expect($verb['base'])->not->toBeEmpty()
            ->and($verb['past'])->not->toBeEmpty()
            ->and($verb['participle'])->not->toBeEmpty()
            ->and($verb['level'])->toBeIn(['A2', 'B1', 'B2'])
            ->and($verb['meaning']['pl'])->not->toBeEmpty();
    }
});

it('has well-formed conditionals and wishes exercises', function (string $file) {
    $data = json_decode(file_get_contents(resource_path("content/english/{$file}.json")), true);

    expect($data['sections'])->not->toBeEmpty();

    foreach ($data['sections'] as $section) {
        expect($section['title'])->not->toBeEmpty()
            ->and($section['theory'])->not->toBeEmpty()
            ->and($section['exercises'])->not->toBeEmpty();

        foreach ($section['exercises'] as $exercise) {
            expect($exercise['id'])->not->toBeEmpty()
                ->and($exercise['prompt'])->not->toBeEmpty();

            if ($exercise['type'] === 'choice') {
                expect($exercise['options'])->toContain($exercise['answer']);
            } elseif ($exercise['type'] === 'gap') {
                expect($exercise['prompt'])->toContain('___')
                    ->and($exercise['accepted'])->not->toBeEmpty();
            } else {
                $this->fail("Unknown exercise type [{$exercise['type']}] in {$file}.json #{$exercise['id']}");
            }
        }
    }
})->with(['conditionals', 'wishes']);
