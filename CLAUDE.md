# CLAUDE.md

## Project

`cms` — a blogging platform. Vanilla PHP (no framework), PostgreSQL via PDO.

## Layout

- `public/index.php` — front controller and route table; the only PHP file under `public/`.
- `public/pico.css` — symlink to Pico's classless fuchsia build in `vendor/`
  (`composer require picocss/pico`). Swap the accent color by repointing the
  symlink at another `pico.classless.<color>.min.css`.
- `public/style.css` — post feed rules; everything else is Pico's defaults.
- `src/Router.php` — pattern matching (`/posts/{slug}`), one segment per
  placeholder, plus per-route middleware run before the handler.
- `src/Db.php` — lazy PDO singleton, configured from environment.
- `src/Post.php` — post queries.
- `src/Auth.php` — password check, session login, CSRF tokens.
- `src/Setting.php` — key/value site settings (`site_name`), cached per request.
- `src/render.php` — `render()` (template + layout) and `e()` (HTML escaping).
- `templates/` — plain PHP templates. `layout.php` wraps the rendered `$content`.
- `templates/admin/` — login form, post list, post editor.
- `migrations/` — numbered plain SQL, applied by `bin/migrate`.
- `bin/migrate` — applies pending migrations in filename order, one transaction
  each, recording every applied file in `schema_migrations`.
- `bin/create-user` — creates the author account or resets its password.
- `docker/initdb/` — runs once on first boot of an empty volume; creates `cms_test`.
- `compose.yaml` — local PostgreSQL 18 (`cms` and `cms_test` databases).
- `tests/` — PHPUnit. Database tests run against `cms_test`.

## Commands

```sh
docker compose up -d                     # postgres on localhost:5432
composer install
php bin/migrate                          # migrate the dev database
php bin/create-user you@example.com pw   # create or reset the author account
php -S localhost:8000 -t public          # dev server
composer test                            # migrate cms_test, then phpunit
```

`docker compose down -v` wipes both databases and re-runs `docker/initdb/`.

## Conventions

- Escape every value rendered into a template with `e()`. Templates receive
  `$data` keys as local variables.
- Post bodies are Markdown, rendered per request by `markdown()` in
  `src/render.php`. It escapes raw HTML itself, so a body takes `markdown()`
  instead of `e()`, never both.
- All SQL goes through prepared statements; never interpolate into a query.
- Database config comes from `DATABASE_DSN`, `DATABASE_USER`, `DATABASE_PASSWORD`;
  defaults match `compose.yaml` (`cms`/`cms`/`cms` on localhost:5432).
- New migrations are `migrations/NNN_name.sql`, numbered in sequence. They are
  never edited after being applied — add a new one instead.
- Database tests extend the pattern in `tests/PostTest.php`: open a transaction
  in `setUp()`, roll it back in `tearDown()`, so rows do not leak between tests.
- A post's state lives entirely in `posts.published_at`: NULL is a draft, a
  future timestamp is scheduled, a past timestamp is live. There is no status
  column and no publishing worker — `published_at <= now()` is evaluated per
  request.
- Public queries must filter on `published_at`. `Post::publishedBySlug()` does;
  `Post::byId()` and `Post::all()` do not and are admin-only.
- Guards are route middleware, not handler code: pass them as the fourth
  argument to `$router->add()`. Every route under `/admin` carries
  `Auth::requireLogin(...)`, and every POST route carries `Auth::requireCsrf(...)`,
  login first so a logged-out visitor is redirected rather than shown a 403.
