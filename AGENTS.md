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
- `resources/js/composables/` - `useTrans()` reads the `translations` Inertia prop.
- `resources/content/` - exercise/game data as JSON (not DB), e.g. alphabet letters, irregular verbs.
- `lang/{pl,cs,sk,de}.json` - UI strings only, shared to Vue via `HandleInertiaRequests::share()` as the `translations` prop for the current `app()->getLocale()`. Content-level translations (exercise meanings) live inline in the `resources/content/*.json` files instead, keyed per language.
- `public/legacy/` - the original static HTML games (pre-Laravel), kept reachable while being ported page by page. Delete once a page's Vue port reaches parity (see plan Etap 2).

## Conventions

- Design tokens (colors, radius) carried over from the legacy static pages live in `resources/css/app.css` under `@theme` (Tailwind v4 generates utilities from them, e.g. `bg-paper`, `text-ink-soft`, `rounded-app`).
- Only `pl` has translations right now; the language switcher UI shows cs/sk/de as disabled ("wkrótce") until Etap 6.
- No CI yet in this repo - run Pint + Pest locally before every commit.
