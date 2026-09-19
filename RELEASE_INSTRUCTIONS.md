# Wdrożenie na produkcję

Ten plik opisuje kroki ręczne wokół deployu - rzeczy, których Forge/Ansible same nie zrobią.

## Wymagania serwera

- PHP 8.2+ (projekt używa Laravel 13, PHP 8.4 w dev)
- Composer 2
- Node 18+ / npm (tylko do budowania assetów przy deployu, nie w runtime)
- SQLite (start) lub MySQL 8 (gdy ruch urośnie - patrz plan Etap 7)

## Zmienne środowiskowe (`.env` na serwerze)

Skopiować z `.env.example` i uzupełnić:
- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL=https://<docelowa-domena>`
- `APP_KEY` - wygenerować `php artisan key:generate` **raz**, nie kopiować z dev
- `DB_CONNECTION=sqlite` (plik `database/database.sqlite`, utworzyć i `chmod` dla grupy web) lub dane MySQL
- `SESSION_DRIVER=database`, `CACHE_STORE=database` (jak w dev)
- `MAIL_MAILER` - ustawić realny driver, żeby reset hasła (Etap 4) faktycznie wysyłał maile; domyślnie `log` tylko zapisuje do pliku

## Kroki deployu (Forge deploy script / Ansible playbook)

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Health check: `GET /up` (wbudowane w Laravel, skonfigurowane w `bootstrap/app.php`).

## Backup bazy

SQLite: kopiować plik `database/database.sqlite` (np. cron + rsync/S3). Bez tego reset serwera = utrata wszystkich kont, punktów i rankingu.

## Co NIE jest jeszcze zrobione (wymaga decyzji Roberta, patrz plan Etap 7)

- Brak serwera/site'u Forge dla szkola (sprawdzono `forge.py` - nie istnieje)
- Brak monitoringu OhDear dla szkola
- Brak projektu Flare dla szkola (błędy produkcyjne nigdzie nie trafiają)
- Istniejący Ansible playbook (`~/Tools/ansible/playbooks/szkola_site.yml`) obsługuje tylko starą, statyczną wersję strony (kopiuje 3 pliki HTML) - nie nadaje się do Laravela bez przepisania na wzór `templates/nginx/laravel_site.conf.j2` (używanego np. przez ecdonline)
- Pod `szkola.dev.interwal.net` wciąż działa stara statyczna strona - podmiana na Laravela to świadoma decyzja o momencie (przestaje działać stary link, dopóki nowy deploy nie wjedzie)
