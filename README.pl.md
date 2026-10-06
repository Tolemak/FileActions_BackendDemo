# FileActions

Wrzucasz obrazek, dostajesz go z powrotem przeskalowany, skonwertowany, skompresowany, obrócony albo w sepii. Symfony 7.4 + Imagick za małym API, frontend w TypeScript + FilePond, PL/EN. [file-actions.tolemak.pl](https://file-actions.tolemak.pl/)

[English version](README.md)

## Uruchomienie

```bash
cd container
docker compose up -d --build
docker compose exec web-server composer install
cd .. && npm install && npm run build
```

Aplikacja pod `http://localhost:40055`. Domyślny obraz jest produkcyjny; z Xdebugiem (port 9000, pasuje do `.vscode/launch.json`) trzeba dodać `-f docker-compose.yml -f docker-compose.dev.yml`.

Bez Dockera: PHP 8.3 z `ext-imagick`, potem `composer install`, `npm run build` i `php -S 127.0.0.1:8000 -t public`. `.env` domyślnie ma `prod`, dla profilera ustaw `APP_ENV=dev` w `.env.local`.

Obraz produkcyjny: po zielonych testach na `master` CI buduje cały runtime z `container/Dockerfile.app` (`vendor` z Composera, assety Vite, Apache na porcie 8080 bez roota), wypycha go jako `ghcr.io/tolemak/fileactions-app:<sha commita>` (i `:latest`) i dołącza podpisane poświadczenie pochodzenia builda. Serwer sam pobiera obraz i weryfikuje poświadczenie; CI nigdy się z nim nie łączy.

## API

Jeden obrazek (`jpeg`/`png`/`gif`, do 5 MB) jako `multipart/form-data`, wynik wraca jako plik do pobrania:

```
POST /file/resize/{size}          10-300 (%)
POST /file/convert/{extension}    jpeg | png | gif
POST /file/compress/{ratio}       1-100
POST /file/rotate/{degrees}       0-360
POST /file/sepia/{intensity}      1-100
```

## Dodawanie akcji

Akcja to jedna klasa PHP w `src/Action/` implementująca `App\Action\FileAction`. Symfony nadaje jej tag `app.file_action` przez autokonfigurację, a reszta jest generyczna: trasa `POST /file/{action}[/{value}]`, strona `/file-view/{action}`, pozycja w nawigacji, karta na stronie głównej i jedna aplikacja TypeScript, która czyta schemat opcji z atrybutów `data-*`.

```php
final class BlurAction implements FileAction
{
    public function name(): string { return 'blur'; }

    public function spec(): string { return '1-20'; }

    public function option(): ActionOption { return ActionOption::range(1, 20, 1, 5); }

    public function process(Imagick $image, int|string|null $value): void
    {
        $image->blurImage((int) $value, 1);
    }
}
```

- `option()` zwraca `ActionOption::range($min, $max, $step, $default)` (suwak), `ActionOption::choice(['wartość' => 'Etykieta'])` (lista wyboru na stronie) albo `null` dla akcji bez parametru (`POST /file/{action}`).
- Akcja zmieniająca format wyjściowy implementuje też `ChangesOutputFormat`, zob. `ConvertAction`.
- Opcjonalne `#[AsTaggedItem(priority: n)]` ustala miejsce w nawigacji; bez niego akcja ląduje na końcu.
- Tłumaczenia w `translations/messages.en.yaml` i `messages.pl.yaml`: `tools.{name}`, `home.card.{name}.text`, `{name}.title`, `{name}.header`, `{name}.description`, `{name}.button`, `error.{name}_failed`, a dla akcji z opcją `error.{name}_invalid` oraz `{name}.prompt_title` (range) lub `{name}.option_label` (choice).

To cała zmiana: jedna klasa i dwa bloki tłumaczeń. `tests/Fixtures/GrayscaleAction.php` jest właśnie taką akcją, zarejestrowaną tylko w środowisku `test` (`when@test` w `config/services.yaml`), a `tests/Action/AddingAnActionTest.php` dowodzi, że dostaje trasę, stronę, pozycję w nawigacji i kartę bez żadnego innego kodu.

## Testy

```bash
npm run build     # testy stron potrzebują zbudowanych assetów
php bin/phpunit
npm test
```

TypeScript 7 czeka na wsparcie w typescript-eslint, więc Dependabot pomija większe aktualizacje TypeScripta do tego czasu.
