<?php

use App\Http\Middleware\SetLocale;

it('has well-formed irregular verb data with all locales translated', function () {
    $data = json_decode(file_get_contents(resource_path('content/english/irregular-verbs.json')), true);

    expect($data['verbs'])->not->toBeEmpty();

    foreach ($data['verbs'] as $verb) {
        expect($verb['base'])->not->toBeEmpty()
            ->and($verb['past'])->not->toBeEmpty()
            ->and($verb['participle'])->not->toBeEmpty()
            ->and($verb['level'])->toBeIn(['A2', 'B1', 'B2']);

        foreach (SetLocale::SUPPORTED as $locale) {
            expect($verb['meaning'][$locale] ?? null)->not->toBeEmpty();
        }
    }
});

it('has well-formed conditionals and wishes exercises with all locales translated', function (string $file) {
    $data = json_decode(file_get_contents(resource_path("content/english/{$file}.json")), true);

    expect($data['sections'])->not->toBeEmpty();
    foreach (SetLocale::SUPPORTED as $locale) {
        expect($data['subtitle'][$locale] ?? null)->not->toBeEmpty();
    }

    foreach ($data['sections'] as $section) {
        foreach (SetLocale::SUPPORTED as $locale) {
            expect($section['title'][$locale] ?? null)->not->toBeEmpty()
                ->and($section['theory'][$locale] ?? null)->not->toBeEmpty();
        }
        expect($section['exercises'])->not->toBeEmpty();

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

it('has the same UI translation keys in every language file', function () {
    $reference = json_decode(file_get_contents(lang_path('pl.json')), true);
    $referenceKeys = array_keys($reference);

    foreach (array_diff(SetLocale::SUPPORTED, ['pl']) as $locale) {
        $translated = json_decode(file_get_contents(lang_path("{$locale}.json")), true);

        expect(array_keys($translated))->toEqualCanonicalizing($referenceKeys);
    }
});
