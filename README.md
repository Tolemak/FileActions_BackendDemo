# FileActions

Upload an image, get it back resized, converted, compressed, rotated or in sepia. Symfony 7.4 + Imagick behind a small API, with a TypeScript + FilePond frontend, PL/EN. [file-actions.tolemak.pl](https://file-actions.tolemak.pl/)

[Polska wersja](README.pl.md)

## Running

```bash
cd container
docker compose up -d --build
docker compose exec web-server composer install
cd .. && npm install && npm run build
```

App on `http://localhost:40055`. The default image is production; for Xdebug (port 9000, matches `.vscode/launch.json`) add `-f docker-compose.yml -f docker-compose.dev.yml`.

Without Docker: PHP 8.3 with `ext-imagick`, then `composer install`, `npm run build` and `php -S 127.0.0.1:8000 -t public`. `.env` defaults to `prod`, set `APP_ENV=dev` in `.env.local` for the profiler.

## API

A single image (`jpeg`/`png`/`gif`, up to 5 MB) as `multipart/form-data`, the processed file comes back as a download:

```
POST /file/resize/{size}          10-300 (%)
POST /file/convert/{extension}    jpeg | png | gif
POST /file/compress/{ratio}       1-100
POST /file/rotate/{degrees}       0-360
POST /file/sepia/{intensity}      1-100
```

## Tests

```bash
npm run build     # page tests need the built assets
php bin/phpunit
npm test
```
