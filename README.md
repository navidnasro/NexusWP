# NexusWP

A small, opinionated WordPress plugin framework: PSR-4 autoloading, a fluent
query builder, timestamped migrations and seeders, REST and rewrite routing,
lazy hooks/events/jobs, and a Vue 3 + Vite front end.

The plugin is only the application layer. All framework internals live in the
separate [`nexuswp/core`](core) package, so core updates ship through Composer
instead of a full plugin release.

## Requirements

- PHP 8.2+ with `ext-json`
- WordPress 6.4+
- Composer 2.x
- Node 18+ for the front end

## Install

```bash
cd wp-content/plugins
git clone https://github.com/navidnasro/NexusWP.git
cd NexusWP
composer install
npm install && npm run build
```

Activate **NexusWP** in *Plugins*. Migrations run on activation; demo data is
only inserted when `NEXUSWP_RUN_SEEDERS` is `true` in `wp-config.php`.

## Layout

```text
NexusWP.php            plugin header, autoload guard, service boot
Environment.php        plugin paths, table prefix, token lifetime
bootstraps/App.php     boots services and hands config to the core package
app/                   your application: controllers, models, helpers, views
routes/                api.php (REST) and web.php (rewrite rules)
hooks/                 hook registration (lazy, class-strings)
events/                event listener registration (lazy, class-strings)
jobs/                  WP-Cron registration and the stored-job bootstrap
database/              timestamped migrations and seeders
core/                  nexuswp/core package (own composer.json and README)
tests/                 dependency-free framework checks
```

## Routers

Handlers are `[class-string, method]` pairs. The class is autoloaded only when
the route is actually hit, not when the route is registered.

```php
// routes/api.php
Api::register('GET', '/users', [UserController::class, 'index'], true, 'list_users');

// routes/web.php
Web::registerRule('app', [AppController::class, 'render']);
```

REST routes require a bearer token unless the fourth argument is `false`. The
token is verified with `AUTH_KEY` by default; replace it with
`Api::setTokenVerifier()` when you need rotation or revocation.

## Lazy hooks, events and jobs

Registration stores a class name, so a request never loads handlers it will
not run:

```php
HookManager::action('init', DisableEmojis::class, 20);
HookManager::filter('the_content', ContentFilter::class);

EventListener::on('user.registered', UserRegistered::class);
EventListener::trigger('user.registered', $userId);
```

The class is constructed on first fire and the instance is reused afterwards.
Jobs follow the same rule: `AbstractJob::restoreRegisteredActions()` touches the
registry option only when the request is a cron request.

Because `Foo::class` does not trigger autoloading, these calls are cheap. The
event name is passed to `EventListener::on()` explicitly for exactly that
reason — reading `Foo::NAME` would load the class during registration.

## Commands

```bash
composer test          # php tests/framework_test.php
composer lint          # php -l on every PHP file
npm run dev            # Vite dev server
npm run build          # build into resources/
```

## Configuration

Define these in `wp-config.php`, never in the repository:

- `NEXUSWP_TRUSTED_PROXIES` — array of proxy IPs allowed to supply
  `X-Forwarded-For`.
- `NEXUSWP_RUN_SEEDERS` — `true` to insert demo rows on activation.

## The core package

`core/` is a standalone Composer package (`nexuswp/core`) with its own
namespace `nexuswp\core\` and no references back into the plugin. The plugin
hydrates it once in `bootstraps/App.php`:

```php
Config::init([
    'pluginRoot'    => Environment::PluginRoot,
    'tablePrefix'   => Environment::TablePreFix,
    'storagePath'   => Environment::Storage,
    'migrationPath' => Environment::PluginRoot.'/database/migrations',
    'authKey'       => AUTH_KEY,
]);
```

Until the package has its own repository it is wired as a path repository in
`composer.json`. To split it:

```bash
git subtree split --prefix=core -b core
git push git@github.com:navidnasro/nexuswp-core.git core:main
```

Then replace the `repositories` block with a VCS or Packagist entry and keep
the `nexuswp/core` requirement unchanged.

## Tests

`tests/framework_test.php` runs without WordPress, Composer or a database. It
covers token encoding, the query builder, lazy hook/event registration and
route handler resolution. Add a `check('description', $condition)` line for
each new rule.

## License

MIT — see [LICENSE](LICENSE).
