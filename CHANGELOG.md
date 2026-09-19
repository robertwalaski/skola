# Changelog

Wszystkie znaczące zmiany w tym projekcie są odnotowywane w tym pliku.

Format oparty o [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
projekt stosuje [Semantic Versioning](https://semver.org/lang/pl/).

## [Unreleased]

## [0.1.0] - 2026-09-19

### Added
- Szkielet aplikacji Laravel + Inertia + Vue 3 + Tailwind v4, zastępujący dotychczasowe statyczne strony HTML.
- Strona główna z kafelkami kursów (matematyka, angielski dla dzieci) i sekcją "Dla dorosłych" (czasowniki nieregularne, Conditionals, Wishes - zapowiedź).
- Struktura i18n: tłumaczenia UI w `lang/pl.json`, przekazywane do Vue jako props przez Inertia; przełącznik języka (PL aktywny, CS/SK/DE jako "wkrótce").
- Stare gry (`tabliczka.html`, `alfabet.html`, `index.html`) przeniesione do `public/legacy/` i nadal dostępne pod dotychczasowymi ścieżkami do czasu przepisania na komponenty Vue.
