## Simple PHP container with Xdebug
A simple container for PHP projects with Xdebug.

## Run command
`docker-compose up -d` or `podman-compose up -d`

## Notes
- Current Xdebug version: 3.3.2 (2024-06-06),
- HTTP server: Apache,
- Set the IP address for the debugging computer (`xdebug.client_host`) in the `php/php.ini-development`,
- If you want to change PHP version edit the `php/Dockerfile` (current PHP version: 8.3.7), 
- If you want to change directory for PHP project change `volumes` value in `docker-compose.yml` file. 
- To change default port (8080) edit the `docker-compose.yml` file.
- To connect use address `localhost:8080`.