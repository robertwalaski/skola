# Szkoła

Gry i ćwiczenia edukacyjne: matematyka i angielski dla dzieci, gramatyka angielska dla dorosłych (czasowniki nieregularne, conditionals, wishes). Docelowo konta uczniów, punkty i ranking, wielojęzyczny interfejs (PL/CS/SK/DE).

Stack: Laravel 13 + Inertia + Vue 3 + Tailwind v4.

## Uruchomienie lokalne

Projekt uruchamia się przez [Herd](https://herd.laravel.com) pod `http://szkola.test/`.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev   # albo npm run build
```

## Testy

```bash
php artisan test --compact
vendor/bin/pint --test
```

Więcej informacji dla developerów (struktura, konwencje, ClickUp) w `AGENTS.md`.
