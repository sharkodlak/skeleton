# Skeleton for PHP projects

This repository is intentionally kept framework-agnostic on the main branch.
The shared project structure, conventions, and base configuration live here.
Framework-specific implementations are maintained in dedicated branches created from main.
Use main as the baseline when creating a new framework-specific branch.

## Getting started

```bash
make install
```

`make install` copies `.env.example` to `.env` (only when `.env` does not exist yet)
and starts the containers. `.env` is not versioned — keep local secrets there and
mirror every new variable back into `.env.example`.

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
