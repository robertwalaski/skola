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

## Accounts & points (Etap 4)

- Login is **nick + PIN** (`pin_hash` column, 4-6 digits) for everyone, including children - no email required, no personal data. Adults may *additionally* set an email + password at registration (`is_adult` flag) purely to enable Laravel's built-in password-reset flow (`Password::sendResetLink`/`Password::reset`, `App\Http\Controllers\Auth\PasswordResetController`) - nick+PIN accounts recover via their teacher (Etap 5: `GroupController::resetStudentPin`).
- `App\Http\Controllers\Auth\AuthController` handles register/login/logout. Login is throttled 5/min per nick+IP via `RateLimiter` (same pattern as Fortify). `App\Support\NicknameFilter` blocks a baseline profanity/impersonation list - not exhaustive, extend `NicknameFilter::BLOCKED` as needed.
- **Points are always computed server-side** (`App\Support\Scoring::pointsFor()`, a flat per-course/section table) - a game POSTs `{course, section, exercise_key, correct}` to `/attempts` (`AttemptController`), the client-sent `correct` boolean is the only input trusted, any `points` field in the request is ignored. `section` must be one of `Scoring::sections($course)` or the request is rejected (422).
- `resources/js/composables/useAttempts.js` is the client side of this - `record(course, section, exerciseKey, correct)` is a no-op for guests (`page.props.auth.user` is null) and a fire-and-forget `fetch('/attempts')` otherwise. Every game calls it on each answered exercise: `multiplication.js` via an `onAttempt` callback passed into `useMultiplicationGame()`, `Alphabet.vue`/`IrregularVerbs.vue` inline in their answer handlers, `GrammarLesson.vue` in `onAnswered()` (needs a `section` prop from the page).
- Guests keep the old localStorage-only scoring (`tabliczka.v1`, `abc_alfabet_progress_v2`) untouched - registering migrates it **once**, as a single capped (max 500) `guest-import` attempt row, not a per-answer replay.
- `HandleInertiaRequests` shares `auth.user` (`{nick, points}` or `null`) to every page; `AppLayout.vue` renders the login/register links or nick+points+logout from it.

## Groups & ranking (Etap 5)

- Any logged-in user becomes a `teacher` (role flips on `POST /nauczyciel/klasy`) simply by naming a class - no separate approval step. `App\Models\Group` has a random unambiguous `join_code` (`Group::generateJoinCode()`, excludes 0/O/1/I).
- A student belongs to at most **one** group at a time even though the underlying `group_user` table is many-to-many (`User::groups()`) - joining a new one replaces the old via `sync()`. `User::currentGroup()` is that convention's read side; don't add multi-group UI without revisiting this.
- Teacher can't see a child's PIN (it's hashed) - `GroupController::resetStudentPin` only generates and flashes a new one once, for the teacher to relay directly. Scoped to groups the teacher owns (`abort_unless($group->owner_id === ...)`).
- `App\Http\Controllers\RankingController` returns **only nick + points**, nothing else about a user - global (top 50) and, if the viewer is in a group, that group's ranking too, both filterable by `?period=week|all` (`withSum` scoped by `created_at >=` start of week).
- Shared `status` Inertia prop (`HandleInertiaRequests`) carries one-off flash messages (group created + its code, joined a group, PIN reset) - read it as a plain page prop, e.g. `defineProps({ status })`, or via `usePage().props.status`.

## Multilanguage (Etap 6)

- `App\Http\Middleware\SetLocale` (registered before `HandleInertiaRequests` in `bootstrap/app.php`, so it runs first) resolves the request locale: signed-in user's `learner_locale` -> guest session `locale` -> `config('app.locale')` (`pl`). `SetLocale::SUPPORTED` is the single source of truth for the 4 supported codes - `LocaleController`, the language-file test and the content test all read it, don't hardcode the list elsewhere.
- `POST /jezyk {locale}` (`LocaleController`) sets the session value and, if authenticated, also saves `learner_locale` on the user - so a guest's choice survives their session, a logged-in user's survives forever. `resources/js/Components/LanguageSwitcher.vue` is the PL/CS/SK/DE UI for it, used on `Home.vue`.
- **Scope actually translated**: the Home page and nav chrome (`lang/{pl,cs,sk,de}.json` via `t()`), and exercise *content* - `irregular-verbs.json` meaning per verb, `conditionals.json`/`wishes.json` theory+section titles (both now `{pl,cs,sk,de}` objects, resolved by `GrammarLesson.vue`'s/`IrregularVerbs.vue`'s `localized()`/`meaningOf()` helpers, falling back to `pl`). English exercise sentences themselves are never translated - that's the language being learned.
- **Not translated (known, deliberate limitation)**: in-game UI chrome inside `Multiplication.vue`, `Alphabet.vue`, `IrregularVerbs.vue`, `ExerciseCard.vue`, `Ranking.vue`, `Teacher/Dashboard.vue`, the auth pages, etc. is still hardcoded Polish - it was never routed through `t()` when those pages were built in earlier Etaps. Retrofitting it is a large, separate mechanical pass (100+ strings, several needing `t()` to support interpolation, which it doesn't yet) - out of scope for what Etap 6 committed to (`lang/*.json` + content fields), tracked as follow-up rather than silently declared done.
- cs/sk/de translations (UI strings and content) are machine-translated by Claude, not yet reviewed by a native speaker - flagged in `irregular-verbs.json`'s `"note"` field and here; get them checked before relying on them being idiomatic.
- Tests: `EnglishContentTest` checks every verb/section has all 4 locales non-empty and every `lang/*.json` has the same keys as `pl.json`; `LocaleTest` covers the switch-and-persist flow end to end.

## Conventions

- Design tokens (colors, radius) carried over from the legacy static pages live in `resources/css/app.css` under `@theme` (Tailwind v4 generates utilities from them, e.g. `bg-paper`, `text-ink-soft`, `rounded-app`).
- No CI yet in this repo - run Pint + Pest locally before every commit.
- Feature tests use a real (in-memory sqlite) test DB via `RefreshDatabase` (`tests/Pest.php`) - don't mock the DB.
