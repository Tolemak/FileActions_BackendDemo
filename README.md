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

Production image: after the checks pass on `master`, CI builds the whole runtime in `container/Dockerfile.app` (Composer `vendor`, Vite assets, Apache on port 8080 as a non-root user), pushes it as `ghcr.io/tolemak/fileactions-app:<commit sha>` (and `:latest`) and attaches a signed build provenance attestation. The server pulls the image and verifies the attestation itself; CI never connects to it.

## API

A single image (`jpeg`/`png`/`gif`, up to 5 MB) as `multipart/form-data`, the processed file comes back as a download:

```
POST /file/resize/{size}          10-300 (%)
POST /file/convert/{extension}    jpeg | png | gif
POST /file/compress/{ratio}       1-100
POST /file/rotate/{degrees}       0-360
POST /file/sepia/{intensity}      1-100
```

Images are rejected above 24 megapixels (`PixelBudget::MAX_PIXELS`): a 5 MB upload can still decode to hundreds of megapixels, so dimensions are read from the header before decoding. At 8 bytes per pixel (Q16) this stays well under the 256 MB Imagick memory limit set in `FileService`.

## Adding an action

An action is one PHP class in `src/Action/` that implements `App\Action\FileAction`. Symfony autoconfigures it with the `app.file_action` tag, and everything else is generic: the `POST /file/{action}[/{value}]` route, the `/file-view/{action}` page, the navigation entry, the home card and the single TypeScript sub-app, which reads the option schema from `data-*` attributes.

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

- `option()` returns `ActionOption::range($min, $max, $step, $default)` (a slider prompt), `ActionOption::choice(['value' => 'Label'])` (a select on the page) or `null` for an action without a parameter (`POST /file/{action}`).
- An action that changes the output format also implements `ChangesOutputFormat`, see `ConvertAction`.
- Optional `#[AsTaggedItem(priority: n)]` sets the position in the navigation; without it the action comes last.
- Translations, in `translations/messages.en.yaml` and `messages.pl.yaml`: `tools.{name}`, `home.card.{name}.text`, `{name}.title`, `{name}.header`, `{name}.description`, `{name}.button`, `error.{name}_failed`, and for an action with an option `error.{name}_invalid` plus `{name}.prompt_title` (range) or `{name}.option_label` (choice).

That is the whole change: one class and two translation blocks. `tests/Fixtures/GrayscaleAction.php` is exactly such an action, registered only in the `test` environment (`when@test` in `config/services.yaml`), and `tests/Action/AddingAnActionTest.php` proves it gets a route, a page, a navigation entry and a home card with no other code.

## Tests

```bash
npm run build     # page tests need the built assets
php bin/phpunit
npm test
```

TypeScript 7 waits for typescript-eslint support, so Dependabot skips TypeScript major updates until then.
