# File Actions Demo

*Read this in other languages: [Polski](README.pl.md)*

Symfony 7.2 / PHP 8.3 backend that runs Imagick-backed image operations
(resize, format conversion, compression, rotation, sepia tone) behind a
small REST API, with a Bootstrap + FilePond + TypeScript frontend on top.

## Stack

- **Backend**: Symfony 7.2, PHP 8.3, Imagick (`ext-imagick`)
- **Frontend**: TypeScript, Twig, Vite, FilePond, SweetAlert2, Bootstrap 5
- **i18n**: `symfony/translation`, session-sticky locale switch (`en` / `pl`)
- **Tests**: PHPUnit — unit tests on `FileService`, functional tests on every
  controller (happy path + every validation branch) via `WebTestCase`
- **Containers**: `container/` ships a PHP 8.3 + Apache + Xdebug image
  (Podman/Docker Compose)

Only what's actually used is installed: no Doctrine, no Security bundle,
no Messenger/Mailer/Notifier, no asset-mapper/Stimulus — this app has no
database, no auth, and no background jobs, so those would just be dead
weight and attack surface.

## Endpoints

All under `/file`, accept a single uploaded image (`jpeg`/`png`/`gif`,
≤5MB) via `multipart/form-data`, return the processed file as a binary
download:

| Route | Method | Params |
|---|---|---|
| `/file/resize/{size}` | POST | `size`: 10–300 (% of original) |
| `/file/convert/{extension}` | POST | `extension`: `jpeg`\|`png`\|`gif` |
| `/file/compress/{ratio}` | POST | `ratio`: 1–100 (quality) |
| `/file/rotate/{degrees}` | POST | `degrees`: 0–360 |
| `/file/sepia/{intensity}` | POST | `intensity`: 1–100 |

Errors come back as `400`/`500` with a translated plain-text body
(honors `?_locale=pl`, sticky per session — see `LocaleSubscriber`).
Browsable versions of each action live under `/file-view/*`.

## Running it

**Docker** (matches `container/` exactly, PHP + Imagick + Xdebug preinstalled):

```bash
cd container
docker compose up -d --build
docker compose exec web-server composer install
npm install && npm run build   # on the host — no Node in the container
```

App is served at `http://localhost:40055`. See `container/README.md` for
Xdebug setup and other details.

**Native PHP** — needs PHP 8.3+ with the `imagick` extension and Composer:

```bash
composer install
npm install && npm run build   # or `npm run dev` for a Vite dev server
php -S 127.0.0.1:8000 -t public
```

Copy `.env` to `.env.local` and set `APP_ENV=dev` for the profiler/toolbar
and readable error pages; the checked-in `.env` defaults to `prod`.

## Tests

```bash
php bin/phpunit
```

## Layout

- `src/Controller/FileController.php` — the API, one action per operation,
  shared input validation in `checkTypicalIssues()`
- `src/Service/FileService.php` — the actual Imagick calls, kept separate
  from HTTP concerns so it's unit-testable without booting the kernel
- `src/EventSubscriber/LocaleSubscriber.php` — reads `?_locale`, persists
  it to the session, runs ahead of routing so it still applies on a 404
- `translations/messages.{en,pl}.yaml` — every user-facing string, backend
  error messages included
