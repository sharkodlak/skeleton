# Skeleton for PHP projects

This repository is intentionally kept framework-agnostic on the main branch.
The shared project structure, conventions, and base configuration live here.
Framework-specific implementations are maintained in dedicated branches created from main.
Use main as the baseline when creating a new framework-specific branch.

## Getting started

```bash
make install
```

`make install` creates the two local files from their `.example` counterparts —
`.env` and `docker-compose.override.yml` — and starts the containers. Neither
copy is versioned: keep local secrets and local stack tweaks there, and mirror
every new `.env` variable back into `.env.example`.

`docker-compose.yml` on its own describes the **production** shape of the stack:
the `production` build target, no bind mounts, static assets baked into the nginx
image. The override adds what only development wants — the working tree mounted
over the image, the `development` build target with Xdebug, the host's Composer
cache. What you deploy is therefore what the base file says, not a stripped-down
variant of your dev setup.

Then prepare the databases:

```bash
make db-setup
```

## Databases

Two databases are created from the same Phinx migrations:

| Database        | Purpose              | Phinx environment |
|-----------------|----------------------|-------------------|
| `$DB_NAME`      | development          | `development`     |
| `$DB_NAME_test` | automated test suite | `testing`         |

The postgres image creates both when the data directory is initialised for the
first time; `make db-create-test` adds the test database to a cluster that
already exists. The test database name is always `DB_NAME` with `_test`
appended, so there is a single variable to configure.

Tests never touch the development database. [tests/bootstrap.php](tests/bootstrap.php)
rewrites `DB_NAME` to the `_test` variant before anything else runs, and
[tests/integration/IntegrationTestCase.php](tests/integration/IntegrationTestCase.php)
refuses to connect to a database whose name does not end in `_test`, so a broken
setup fails the test instead of quietly mutating development data. That base
class also offers `truncateAllTables()` for a clean slate between tests.

Run `make db-migrate-test` after adding a migration so the test database keeps up.

## Container startup

Both the development and the production image run the same entrypoint,
[containers/images/php/init.sh](containers/images/php/init.sh). It installs
Composer dependencies when they are missing or older than `composer.lock` (the
production image bakes them in at build time, so it skips), waits for the
database to accept connections, and only then execs `php-fpm`, which therefore
runs as PID 1 and receives signals. Any failing step aborts the startup instead
of leaving a half-initialised container serving requests.

Migrations are not part of that sequence — schema changes stay an explicit
`make db-setup`, never a side effect of starting a container.

Once the sequence finishes, the script creates `/tmp/app-ready`. That file is
the `php` service's healthcheck, so `web` waits for a genuinely initialised
application rather than for a process that merely started. `db` reports health
through `pg_isready`, and `php` waits for it. The wait loop inside `init.sh`
covers the same ground for environments that run the image without Compose.

## Test suites

| Suite | Command | Part of `cmd:qa` | What it needs |
|-------|---------|------------------|---------------|
| Unit | `cmd:tests:unit:parallel` | `qa:fast` | nothing |
| Integration | `cmd:tests:integration` | `qa:mid` | the migrated test database |
| E2e | `cmd:tests:e2e` | `qa:slow` | the local stack running (`make up`) |
| Smoke | `cmd:tests:smoke` | **no** | a deployed environment |

E2e drives the stack the way a browser does — over HTTP, through nginx, into
php-fpm — against the stack `make up` starts, so it may create whatever state it
needs. Point it elsewhere with `E2E_BASE_URL`.

Smoke is a read-only probe of an already-deployed environment and is
deliberately outside `cmd:qa`: QA runs in fresh, CI-like environments where
nothing is deployed, so a smoke check there would either fail or be aimed at a
stack it should not touch. It skips itself unless `SMOKE_BASE_URL` is set:

```bash
SMOKE_BASE_URL=https://example.com make smoke
```

Smoke tests must never write — the target may well be production.
