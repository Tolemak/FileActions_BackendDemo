# File Actions Demo

*Dostępne również w: [English](README.md)*

Backend Symfony 7.2 / PHP 8.3 wykonujący operacje na obrazach oparte na
Imagick (zmiana rozmiaru, konwersja formatu, kompresja, obrót, filtr
sepia) udostępnione jako niewielkie REST API, plus frontend na bazie
Bootstrap + FilePond + TypeScript.

## Stack

- **Backend**: Symfony 7.2, PHP 8.3, Imagick (`ext-imagick`)
- **Frontend**: TypeScript, Twig, Vite, FilePond, SweetAlert2, Bootstrap 5
- **i18n**: `symfony/translation`, przełącznik języka trzymany w sesji (`en` / `pl`)
- **Testy**: PHPUnit — testy jednostkowe `FileService`, testy funkcjonalne
  każdego kontrolera (happy path + każda gałąź walidacji) przez `WebTestCase`
- **Kontenery**: `container/` zawiera obraz PHP 8.3 + Apache + Xdebug
  (Podman/Docker Compose)

Zainstalowane jest tylko to, co faktycznie jest używane: bez Doctrine, bez
bundla Security, bez Messenger/Mailer/Notifier, bez asset-mapper/Stimulus —
ta aplikacja nie ma bazy danych, autoryzacji ani zadań w tle, więc byłby to
tylko martwy balast i niepotrzebna powierzchnia ataku.

## Endpointy

Wszystkie pod `/file`, przyjmują jeden przesłany obraz (`jpeg`/`png`/`gif`,
≤5MB) przez `multipart/form-data`, zwracają przetworzony plik jako pobieranie
binarne:

| Route | Metoda | Parametry |
|---|---|---|
| `/file/resize/{size}` | POST | `size`: 10–300 (% oryginału) |
| `/file/convert/{extension}` | POST | `extension`: `jpeg`\|`png`\|`gif` |
| `/file/compress/{ratio}` | POST | `ratio`: 1–100 (jakość) |
| `/file/rotate/{degrees}` | POST | `degrees`: 0–360 |
| `/file/sepia/{intensity}` | POST | `intensity`: 1–100 |

Błędy wracają jako `400`/`500` z przetłumaczoną treścią tekstową (respektuje
`?_locale=pl`, trzymane w sesji — zobacz `LocaleSubscriber`). Przeglądarkowe
wersje każdej akcji są dostępne pod `/file-view/*`.

## Uruchomienie

**Docker** (dokładnie to, co jest w `container/`, PHP + Imagick + Xdebug już
zainstalowane):

```bash
cd container
docker compose up -d --build
docker compose exec web-server composer install
npm install && npm run build   # na hoście — w kontenerze nie ma Node
```

Aplikacja dostępna pod `http://localhost:40055`. Szczegóły konfiguracji
Xdebug i inne informacje — w `container/README.md`.

**Natywny PHP** — wymaga PHP 8.3+ z rozszerzeniem `imagick` i Composera:

```bash
composer install
npm install && npm run build   # albo `npm run dev` dla serwera deweloperskiego Vite
php -S 127.0.0.1:8000 -t public
```

Skopiuj `.env` do `.env.local` i ustaw `APP_ENV=dev`, żeby mieć profiler,
toolbar i czytelne strony błędów; wersja `.env` w repo domyślnie ustawia `prod`.

## Testy

```bash
php bin/phpunit
```

## Struktura

- `src/Controller/FileController.php` — API, po jednej akcji na operację,
  wspólna walidacja wejścia w `checkTypicalIssues()`
- `src/Service/FileService.php` — właściwe wywołania Imagick, oddzielone od
  warstwy HTTP, dzięki czemu da się je testować jednostkowo bez uruchamiania kernela
- `src/EventSubscriber/LocaleSubscriber.php` — czyta `?_locale`, zapisuje je
  do sesji, działa przed routingiem, więc obowiązuje też na stronie 404
- `translations/messages.{en,pl}.yaml` — wszystkie teksty widoczne dla
  użytkownika, łącznie z komunikatami błędów backendu
