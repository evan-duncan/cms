# CLAUDE.md

## Project

`cms` — a blogging platform. Vanilla PHP (no framework), PostgreSQL via PDO.

## Layout

- `public/index.php` — front controller and route table; the only web-exposed file.
- `src/Router.php` — pattern matching (`/posts/{slug}`), one segment per placeholder.
- `src/Db.php` — lazy PDO singleton, configured from environment.
- `src/Post.php` — post queries.
- `src/render.php` — `render()` (template + layout) and `e()` (HTML escaping).
- `templates/` — plain PHP templates. `layout.php` wraps the rendered `$content`.
- `migrations/` — numbered plain SQL, applied by `bin/migrate`.
- `bin/migrate` — applies pending migrations in filename order, one transaction
  each, recording every applied file in `schema_migrations`.
- `docker/initdb/` — runs once on first boot of an empty volume; creates `cms_test`.
- `compose.yaml` — local PostgreSQL 18 (`cms` and `cms_test` databases).
- `tests/` — PHPUnit. Database tests run against `cms_test`.

## Commands

```sh
docker compose up -d                     # postgres on localhost:5432
composer install
php bin/migrate                          # migrate the dev database
php -S localhost:8000 -t public          # dev server
composer test                            # migrate cms_test, then phpunit
```

`docker compose down -v` wipes both databases and re-runs `docker/initdb/`.

## Conventions

- Escape every value rendered into a template with `e()`. Templates receive
  `$data` keys as local variables.
- All SQL goes through prepared statements; never interpolate into a query.
- Database config comes from `DATABASE_DSN`, `DATABASE_USER`, `DATABASE_PASSWORD`;
  defaults match `compose.yaml` (`cms`/`cms`/`cms` on localhost:5432).
- New migrations are `migrations/NNN_name.sql`, numbered in sequence. They are
  never edited after being applied — add a new one instead.
- Database tests extend the pattern in `tests/PostTest.php`: open a transaction
  in `setUp()`, roll it back in `tearDown()`, so rows do not leak between tests.
