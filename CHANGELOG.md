# Changelog

Wszystkie znaczące zmiany w tym projekcie są odnotowywane w tym pliku.

Format oparty o [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
projekt stosuje [Semantic Versioning](https://semver.org/lang/pl/).

## [Unreleased]

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
