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

## API

Jeden obrazek (`jpeg`/`png`/`gif`, do 5 MB) jako `multipart/form-data`, wynik wraca jako plik do pobrania:

```
POST /file/resize/{size}          10-300 (%)
POST /file/convert/{extension}    jpeg | png | gif
POST /file/compress/{ratio}       1-100
POST /file/rotate/{degrees}       0-360
POST /file/sepia/{intensity}      1-100
```

## Testy

```bash
npm run build     # testy stron potrzebują zbudowanych assetów
php bin/phpunit
npm test
```
