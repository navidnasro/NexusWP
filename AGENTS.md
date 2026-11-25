# Repository Guidelines

NexusWP is a WordPress plugin skeleton (PHP 8.2+) with a Vue 3 + Vite front end.
The plugin namespace is `nexuswp\`; framework internals live in the separate
`nexuswp/core` package under `core/` and autoload from `core/src/`.

## Project Structure

- `NexusWP.php` — plugin entry point and metadata; boots services through
  `bootstraps/App.php`.
- `Environment.php` — plugin paths, table prefix, token lifetime.
- `bootstraps/App.php` — hydrates `nexuswp\core\Config`, registers activation
  hooks, loads route/hook/event/job registration files.
- `app/` — application code: `controllers/`, `controllers/api/`, `models/`,
  `helpers/`, `views/`.
- `routes/` — `api.php` (REST) and `web.php` (rewrite rules).
- `hooks/`, `events/`, `jobs/` — registration files (`register.php`,
  `schedules.php`).
- `database/` — timestamped `migrations/` and `seeders/`.
- `core/` — the `nexuswp/core` package: `composer.json`, `src/`, own README.
- `tests/framework_test.php` — dependency-free framework checks.

## Build and Test

- `composer install` — install PHP dependencies and generate `vendor/autoload.php`.
- `npm install` / `npm run dev` / `npm run build` — front end.
- `composer test` or `php tests/framework_test.php` — framework checks.
- `composer lint` — `php -l` over every PHP file.

`core/` is currently a path repository. Do not add plugin classes or constants
to it: pass values through `Config`. To publish a core change, run the tests and
then split the folder with `git subtree split --prefix=core -b core`.

## Coding Style

- 4-space indentation, PSR-4/PSR-12, `StudlyCase` classes, `camelCase` methods,
  `snake_case` columns. Opening brace on the next line.
- New classes go under the matching directory, e.g.
  `nexuswp\app\controllers\api\PaymentController`.
- Persist through `core\database\model\Model` subclasses with a static `$table`;
  use the query builder (`User::where('role','customer')->first()`).
- Use WordPress APIs (`get_option`, `dbDelta`, `flush_rewrite_rules`) over raw
  equivalents.
- No linter is configured; avoid reformatting untouched files.

## Lazy Registration Rules

Registration must never autoload a handler. These APIs intentionally take an
explicit name plus a class-string; `Foo::class` does not autoload, while
`Foo::CONST` does.

```php
HookManager::action('init', DisableEmojis::class, 20);
EventListener::on('user.registered', UserRegistered::class);
Api::register('GET', '/users', [UserController::class, 'index'], true, 'list_users');
Web::registerRule('app', [AppController::class, 'render']);
```

Keep new registration files free of constructor calls and top-level `new`. Cron
work belongs behind `AbstractJob::restoreRegisteredActions()`, which is a no-op
outside cron requests.

## Configuration

- `NEXUSWP_TRUSTED_PROXIES` — proxy IPs allowed to supply `X-Forwarded-For`.
- `NEXUSWP_RUN_SEEDERS` — set to `true` only when demo data is wanted.
- Migrations run on activation; bump the `Version` header and add a timestamped
  migration for schema changes.

## Testing

`tests/framework_test.php` runs with plain PHP, no WordPress or Composer. Add a
`check('description', $condition)` line for each new rule. Cover migration
`up()`/`down()` paths and REST controllers with invalid input.

## Commits

Short imperative subjects under 72 characters, one subsystem per commit, e.g.
`add user wallet migration`. Do not commit credentials or `vendor/`.

## Security

- Never commit secrets; read them from constants defined in `wp-config.php`.
- REST routes default to `edit_users`; always pass a narrower capability.
- Bearer tokens expire after `Environment::TokenTtl`; there is no non-expiring
  token path.
- Sanitize and escape REST input/output and verify capabilities in controllers.
