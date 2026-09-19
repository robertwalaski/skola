<?php

namespace App\Support;

/**
 * Server-authoritative points per correct answer, by course/section. The
 * client only reports whether an attempt was correct - it never sends a
 * point value the server trusts (see AttemptController).
 */
class Scoring
{
    private const RULES = [
        'math' => [
            'multiplication' => 10,
        ],
        'english' => [
            'alphabet' => 5,
            'irregular-verbs' => 8,
            'conditionals' => 6,
            'wishes' => 6,
        ],
    ];

    public static function courses(): array
    {
        return array_keys(self::RULES);
    }

    public static function sections(string $course): array
    {
        return array_keys(self::RULES[$course] ?? []);
    }

    public static function pointsFor(string $course, string $section, bool $correct): int
    {
        if (! $correct) {
            return 0;
        }

        return self::RULES[$course][$section] ?? 0;
    }
}
