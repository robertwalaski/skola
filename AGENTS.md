# Szkola

Educational games/exercises for kids (math, English alphabet) and adults (English grammar). Laravel + Inertia + Vue 3 + Tailwind v4.

## Stack & commands

- PHP 8.4, Laravel 13, SQLite (dev). Served locally via Herd at http://szkola.test/
- `npm run dev` - Vite dev server. `npm run build` - production assets.
- `php artisan test --compact` - Pest suite. `vendor/bin/pint` - style fix, `vendor/bin/pint --test` - check only.
- ClickUp project: list `szkola` (id `1200690000005291`) in space "Projekty". `cu.py --project szkola ...`. Roadmap/plan: `~/.claude/plans/pasted-content-id-38e6-chc-do-starry-meteor.md`.

## Structure

- `resources/js/Pages/*.vue` - Inertia pages (one per route).
- `resources/js/Layouts/AppLayout.vue` - shared shell (header/back-link/footer).
- `resources/js/Components/` - reusable UI (e.g. `CourseTile.vue`).
- `resources/js/composables/` - `useTrans()` reads the `translations` Inertia prop, `useSpeech()` wraps the Web Speech API (voice pick, `speakSequence()` chained on real `onend`, not a fixed timeout).
- `resources/js/games/` - per-game logic kept out of the .vue file (e.g. `multiplication.js`, `alphabetProgress.js`), so it's testable/reusable independent of the component.
- `resources/content/` - exercise/game data as JSON (not DB): `english/alphabet.json`, `english/irregular-verbs.json` (level A2/B1/B2, self-assigned - source table has no levels), `english/conditionals.json` + `english/wishes.json` (theory + exercises per section). Covered by a content-integrity Pest test (`tests/Feature/EnglishContentTest.php`) so a typo in the data fails CI-locally, not silently in the browser.
- `resources/js/Components/ExerciseCard.vue` + `GrammarLesson.vue` - shared engine for the two grammar lessons (Conditionals, Wishes): `type: "choice"` (pick the right option) or `"gap"` (fill the blank, `accepted` is a list of accepted strings, matched case/whitespace-insensitively). A lesson page is just `<GrammarLesson :content="conditionalsJson" />` - see `Pages/English/Conditionals.vue`.
- `lang/{pl,cs,sk,de}.json` - UI strings only, shared to Vue via `HandleInertiaRequests::share()` as the `translations` prop for the current `app()->getLocale()`. Content-level translations (exercise meanings) live inline in the `resources/content/*.json` files instead, keyed per language.

## Conventions

- Design tokens (colors, radius) carried over from the legacy static pages live in `resources/css/app.css` under `@theme` (Tailwind v4 generates utilities from them, e.g. `bg-paper`, `text-ink-soft`, `rounded-app`).
- Only `pl` has translations right now; the language switcher UI shows cs/sk/de as disabled ("wkrótce") until Etap 6.
- No CI yet in this repo - run Pint + Pest locally before every commit.
