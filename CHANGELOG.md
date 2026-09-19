# Changelog

Wszystkie znaczące zmiany w tym projekcie są odnotowywane w tym pliku.

Format oparty o [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
projekt stosuje [Semantic Versioning](https://semver.org/lang/pl/).

## [Unreleased]

## [0.6.0] - 2026-09-19

### Added
- Działający przełącznik języka (PL/CS/SK/DE) - zapamiętywany w sesji dla gościa, na koncie dla zalogowanego ucznia.
- Pełne tłumaczenie interfejsu strony głównej i nawigacji (`lang/{cs,sk,de}.json`).
- Tłumaczenie treści dydaktycznej: znaczenia 86 czasowników nieregularnych oraz teoria/tytuły sekcji Conditionals i Wishes w 4 językach.
- Testy: kompletność kluczy tłumaczeń UI, obecność wszystkich języków w treści, pełny przepływ przełączania języka.

### Known limitation
- Tłumaczenia cs/sk/de są maszynowe (Claude), nie zweryfikowane jeszcze przez native speakera.
- Elementy interfejsu wewnątrz gier (przyciski, komunikaty) pozostają na razie tylko po polsku - przełącznik działa w pełni dla strony głównej i treści dydaktycznej.

## [0.5.0] - 2026-09-19

### Added
- Klasy: dowolny zalogowany użytkownik może założyć klasę (zostaje nauczycielem) i dostaje kod dołączenia; uczeń dołącza kodem (`/dolacz`), jedna aktywna klasa naraz.
- Panel nauczyciela (`/nauczyciel`) - lista uczniów w klasie, ich punkty, data ostatniej aktywności, reset PIN-u ucznia.
- Ranking (`/ranking`) - globalny i (dla zalogowanych w klasie) klasowy, okresy "ten tydzień"/"cały czas", widoczne tylko nicki i punkty.
- Link do rankingu w górnym pasku na każdej stronie.

## [0.4.0] - 2026-09-19

### Added
- Konta: rejestracja nick + PIN (bez danych osobowych), opcjonalny e-mail + hasło dla dorosłych z odzyskiwaniem hasła mailem, logowanie z limitem prób, filtr niedozwolonych nicków.
- `POST /attempts` - serwer nalicza punkty za każdą próbę wg reguły per sekcja (klient zgłasza tylko czy odpowiedź była poprawna).
- Wszystkie gry (tabliczka, alfabet, czasowniki nieregularne, conditionals, wishes) zgłaszają wynik do serwera, gdy uczeń jest zalogowany; gość dalej gra lokalnie jak dotąd.
- Jednorazowe przeniesienie punktów gościa (z localStorage) przy rejestracji, ograniczone do 500 pkt.
- Krótka polityka prywatności (`/prywatnosc`).

## [0.3.0] - 2026-09-19

### Added
- Trzy nowe sekcje angielskiego dla dorosłych: Czasowniki nieregularne (`/angielski/czasowniki-nieregularne` - fiszki, uzupełnij formę, quiz ze słuchu, poziomy A2/B1/B2), Conditionals i Wishes (`/angielski/conditionals`, `/angielski/wishes` - teoria + ćwiczenia).
- Wspólny silnik ćwiczeń (`ExerciseCard.vue` + `GrammarLesson.vue`): typ `choice` (wybór) i `gap` (uzupełnij lukę), z licznikiem poprawnych odpowiedzi na żywo.
- Kafelki "Dla dorosłych" na stronie głównej odblokowane (wcześniej "wkrótce").
- Test integralności danych treści (`EnglishContentTest`).

## [0.2.0] - 2026-09-19

### Added
- Port tabliczki mnożenia (`/matematyka/tabliczka`) na Vue - ta sama logika losowania/punktacji co w wersji statycznej, ten sam klucz `localStorage` (rekord i błędy zachowane), ten sam responsywny układ na telefonie/tablecie w poziomie.
- Port gry alfabetycznej (`/angielski/alfabet`) na Vue - fiszki, brakująca literka, quiz ze słuchu; ten sam klucz postępu w `localStorage`.
- `useSpeech()` - odtwarzanie głosowe litera->słowo teraz czeka na faktyczny koniec poprzedniej wypowiedzi (`onend`), zamiast sztywnych 10 sekund jak w starej wersji.

### Removed
- Statyczne strony w `public/legacy/` (zastąpione przez powyższe, funkcje 1:1 zweryfikowane w przeglądarce).

## [0.1.0] - 2026-09-19

### Added
- Szkielet aplikacji Laravel + Inertia + Vue 3 + Tailwind v4, zastępujący dotychczasowe statyczne strony HTML.
- Strona główna z kafelkami kursów (matematyka, angielski dla dzieci) i sekcją "Dla dorosłych" (czasowniki nieregularne, Conditionals, Wishes - zapowiedź).
- Struktura i18n: tłumaczenia UI w `lang/pl.json`, przekazywane do Vue jako props przez Inertia; przełącznik języka (PL aktywny, CS/SK/DE jako "wkrótce").
- Stare gry (`tabliczka.html`, `alfabet.html`, `index.html`) przeniesione do `public/legacy/` i nadal dostępne pod dotychczasowymi ścieżkami do czasu przepisania na komponenty Vue.
