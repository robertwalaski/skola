<?php

namespace App\Support;

/**
 * Baseline blocklist for public nicknames (they appear on the ranking, so
 * unlike a password there is no privacy fallback here) - not exhaustive,
 * a practical starting list rather than a dedicated profanity library.
 */
class NicknameFilter
{
    private const BLOCKED = [
        // Polish vulgarities
        'kurwa', 'chuj', 'pierdol', 'jebac', 'jebany', 'suka', 'dupa', 'cipa', 'pizda',
        // English vulgarities / slurs
        'fuck', 'shit', 'bitch', 'asshole', 'nigger', 'faggot', 'cunt', 'whore', 'retard',
        // Impersonation of staff roles
        'admin', 'moderator', 'nauczyciel', 'teacher',
    ];

    public static function isBlocked(string $nick): bool
    {
        $normalized = self::normalize($nick);

        foreach (self::BLOCKED as $word) {
            if (str_contains($normalized, $word)) {
                return true;
            }
        }

        return false;
    }

    private static function normalize(string $s): string
    {
        $s = mb_strtolower($s);
        $s = strtr($s, ['0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '@' => 'a', '$' => 's']);

        return preg_replace('/[^a-z]/u', '', $s) ?? $s;
    }
}
