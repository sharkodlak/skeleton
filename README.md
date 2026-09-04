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
