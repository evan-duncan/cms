# cms

A small blogging platform. Vanilla PHP (no framework), PostgreSQL via PDO.

## Running it locally

You need Docker, PHP 8.4 or newer, and Composer.

```sh
docker compose up -d                              # postgres 18 on localhost:5432
composer install
php bin/migrate                                   # create the schema
php bin/create-user you@example.com 'your password'
php -S localhost:8000 -t public                   # dev server
```

Then open <http://localhost:8000> for the blog, and
<http://localhost:8000/admin/login> to sign in and write.

`docker compose up -d` creates two databases on first boot: `cms` for
development and `cms_test` for the suite. Connection settings come from
`DATABASE_DSN`, `DATABASE_USER`, and `DATABASE_PASSWORD`, which default to
`cms`/`cms`/`cms` on localhost:5432 — matching `compose.yaml`, so you do not
need to set them.

`bin/create-user` is also how you reset the password: run it again with the
same email.

### Starting over

```sh
docker compose down -v                            # wipes both databases
docker compose up -d
php bin/migrate
php bin/create-user you@example.com 'your password'
```

## Writing

A post's publish date is the only control:

- **empty** — draft, visible only to you
- **a date in the future** — scheduled, appears by itself at that time
- **a date in the past** — live now

Nothing runs in the background to publish a scheduled post. Every public query
filters on `published_at <= now()`, so the post appears the moment its time
arrives.

## Tests

```sh
composer test
```

This migrates `cms_test` and then runs PHPUnit against it. Each database test
runs inside a transaction that is rolled back, so tests leave no rows behind.
