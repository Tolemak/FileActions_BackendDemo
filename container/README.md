## Container

PHP 8.3 + Apache + Imagick, for running this app without installing PHP
natively. `php/Dockerfile` has two targets:

- `prod` (default): opcache, `display_errors=Off`, `expose_php=Off`, no Xdebug
- `dev`: `prod` plus Xdebug and `php.dev.ini` (`display_errors=On`)

## Run

Production image:

```bash
docker compose up -d --build
```

Dev image:

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

Then, from the repo root (not inside the container):

```bash
docker compose -f container/docker-compose.yml exec web-server composer install
npm install && npm run build
```

App is served at `http://localhost:40055`.

## Notes

- Xdebug listens on port 9000 with `xdebug.client_host=host.docker.internal`
  — matches `.vscode/launch.json`'s "Listen for Xdebug" config out of the box.
- To change the PHP version, edit `php/Dockerfile`'s `FROM` line.
- To use a different host port, edit the `ports` mapping in `docker-compose.yml`.
- Container mounts the whole repo at `/var/www/site`, so `var/cache`, `vendor/`
  and `node_modules/` are shared with the host — keep host and container PHP
  versions in sync if you also run the app natively.
