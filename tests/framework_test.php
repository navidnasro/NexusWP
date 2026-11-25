<?php

declare(strict_types=1);

use nexuswp\app\helpers\Utils;
use nexuswp\app\models\Log;
use nexuswp\app\models\User;

// minimal bootstrap: these helpers run without a full WordPress installation
define('AUTH_KEY','test-signing-key');
define('RootUrl','http://example.test/plugin/');
define('DB_NAME','test');
define('DB_USER','test');
define('DB_PASSWORD','test');
define('DB_HOST','localhost');
define('DB_CHARSET','utf8mb4');

spl_autoload_register(function (string $class): void
{
    $prefix = 'nexuswp\\';

    if (!str_starts_with($class,$prefix))
        return;

    $root = dirname(__DIR__);
    $relative = str_replace('\\','/',substr($class,strlen($prefix)));

    // the framework lives in its own package under core/src
    $file = str_starts_with($relative,'core/')
        ? $root.'/core/src/'.substr($relative,5).'.php'
        : $root.'/'.$relative.'.php';

    if (is_file($file))
        require $file;
});

$failures = 0;

function check(string $name, bool $condition): void
{
    global $failures;

    if ($condition)
    {
        echo "PASS  {$name}\n";
        return;
    }

    $failures++;
    echo "FAIL  {$name}\n";
}

// ---------- token tests ----------

$payload = ['user_id' => 7, 'iat' => time(), 'exp' => time() + 60];
$token = Utils::jwtEncode($payload,AUTH_KEY);

$decoded = Utils::jwtDecode($token,AUTH_KEY);
check('roundtrip keeps the claims', is_object($decoded) && (int)$decoded->user_id === 7);
check('token is base64url encoded', preg_match('/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/',$token) === 1);

[$body,$signature] = explode('.',$token);
$signature[0] = $signature[0] === 'A' ? 'B' : 'A';
check('tampered signature is rejected', Utils::jwtDecode($body.'.'.$signature,AUTH_KEY) === false);

$forged = rtrim(strtr(base64_encode(json_encode(['user_id' => 1, 'exp' => time() + 60])),'+/','-_'),'=');
check('forged body is rejected', Utils::jwtDecode($forged.'.'.$signature,AUTH_KEY) === false);

check('expired token is rejected', Utils::jwtDecode(Utils::jwtEncode(['user_id' => 7, 'exp' => time() - 10],AUTH_KEY),AUTH_KEY) === false);
check('token without exp is rejected', Utils::jwtDecode(Utils::jwtEncode(['user_id' => 7],AUTH_KEY),AUTH_KEY) === false);
check('garbage token is rejected', Utils::jwtDecode('not-a-token',AUTH_KEY) === false);
check('empty token is rejected', Utils::jwtDecode('',AUTH_KEY) === false);
check('extra segments are rejected', Utils::jwtDecode($token.'.extra',AUTH_KEY) === false);
check('wrong key is rejected', Utils::jwtDecode($token,'another-key') === false);

// ---------- query isolation tests ----------

class FakeWpdb
{
    public string $prefix = 'wp_';
    public array $queries = [];

    public function prepare(string $sql, ...$args): string
    {
        foreach ($args as $arg)
            $sql = preg_replace('/%[ds]/',is_int($arg) ? (string)$arg : "'".$arg."'",$sql,1);

        return $sql;
    }

    public function get_results($sql) { $this->queries[] = $sql; return []; }
    public function get_row($sql) { $this->queries[] = $sql; return null; }
    public function get_var($sql) { $this->queries[] = $sql; return 0; }
    public function insert(...$args) { return 0; }
    public function update(...$args) { return 0; }
    public function delete(...$args) { return 0; }
}

$GLOBALS['wpdb'] = new FakeWpdb();

User::where('role','customer')->get();
$userSql = $GLOBALS['wpdb']->queries[0];

$GLOBALS['wpdb']->queries = [];
Log::where('type','sms')->get();
$logSql = $GLOBALS['wpdb']->queries[0];

check('user query contains only its own condition', str_contains($userSql,"role = 'customer'") && !str_contains($userSql,'sms'));
check('log query is not polluted by earlier queries', str_contains($logSql,"type = 'sms'") && !str_contains($logSql,'customer'));

$GLOBALS['wpdb']->queries = [];
User::where('role','customer')->get();
User::where('role','customer')->get();
check('repeated queries do not accumulate conditions', substr_count($GLOBALS['wpdb']->queries[1],'role') === 1);

$GLOBALS['wpdb']->queries = [];
User::where('role','customer')->orderBy('id','DESC; DROP TABLE users')->limit(10)->get();
$orderedSql = $GLOBALS['wpdb']->queries[0];
check('order direction is whitelisted', str_contains($orderedSql,'ORDER BY id ASC'));
check('limit is cast to an integer', str_contains($orderedSql,'LIMIT 10 OFFSET 0'));

$threw = false;

try
{
    User::where('id; DROP TABLE users','=',1);
}
catch (InvalidArgumentException $exception)
{
    $threw = true;
}

check('invalid column names are rejected', $threw);

$deprecations = 0;

set_error_handler(function (int $level) use (&$deprecations): bool
{
    if ($level === E_DEPRECATED)
        $deprecations++;

    return true;
});

$hydrated = User::hydrate(['id' => 5, 'phone_number' => '0912']);

restore_error_handler();

check('hydration maps columns to attributes', $hydrated->phone_number === '0912');
check('hydration does not trigger dynamic property deprecations', $deprecations === 0);
check('toArray excludes query state', array_keys($hydrated->toArray()) === ['id','phone_number']);

// --- core package: configuration, tokens, and lazy registration -------------

use nexuswp\core\Config;
use nexuswp\core\hooks\Action;
use nexuswp\core\route\Web;
use nexuswp\core\support\Jwt;

$GLOBALS['wp_hooks'] = [];
$GLOBALS['wp_hook_count'] = 0;

function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
{
    $GLOBALS['wp_hooks'][$hook] = $callback;
    $GLOBALS['wp_hook_count']++;
}

function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
{
    add_action($hook,$callback,$priority,$acceptedArgs);
}

class TestEvent extends \nexuswp\core\events\Event
{
    public const NAME = 'testEvent';
    public static int $constructed = 0;
    public static array $received = [];

    public function __construct()
    {
        self::$constructed++;
    }

    public function handle(...$args): void
    {
        self::$received = $args;
    }
}

class TestHook extends Action
{
    public const HOOK = 'test_hook';
    public static int $constructed = 0;
    public static int $calls = 0;

    public function __construct()
    {
        self::$constructed++;
    }

    public function handle(mixed ...$args): mixed
    {
        self::$calls++;
        return null;
    }
}

class TestController
{
    public function render(): string
    {
        return 'ok';
    }
}

Config::init([
    'pluginRoot'  => '/srv/plugin',
    'tablePrefix' => 'nexuswp_',
    'storagePath' => '/srv/storage',
    'publicPath'  => '/srv/public',
    'textDomain'  => 'jt',
    'authKey'     => 'secret',
]);

check('config prefix is exposed to core', Config::tablePrefix() === 'nexuswp_');
check('migration path defaults to the plugin database folder', Config::migrationPath() === '/srv/plugin/database/migrations');
check('storage path is normalised without a trailing slash', Config::storagePath() === '/srv/storage');

$token = Jwt::encode(['user_id' => 7, 'exp' => time() + 60], 'secret');
check('core jwt roundtrips a signed payload', Jwt::decode($token,'secret')->user_id === 7);
check('core jwt rejects a foreign key', Jwt::decode($token,'other') === false);
check('core jwt rejects a token without exp', Jwt::decode(Jwt::encode(['user_id' => 7],'secret'),'secret') === false);

\nexuswp\core\events\EventListener::on('testEvent', TestEvent::class);
check('event class is not loaded on registration', TestEvent::$constructed === 0);
\nexuswp\core\events\EventListener::trigger('testEvent','a','b');
check('event fires after lazy construction', TestEvent::$constructed === 1 && TestEvent::$received === ['a','b']);
\nexuswp\core\events\EventListener::trigger('testEvent','c');
check('event instance is reused across triggers', TestEvent::$constructed === 1);

\nexuswp\core\hooks\HookManager::action('test_hook', TestHook::class);
check('hook class is not loaded on registration', TestHook::$constructed === 0);
($GLOBALS['wp_hooks']['test_hook'])();
($GLOBALS['wp_hooks']['test_hook'])();
check('hook is built once on first fire and reused', TestHook::$constructed === 1 && TestHook::$calls === 2);

$handler = Web::resolveHandler([TestController::class,'render']);
check('route handler resolves a class-string lazily', $handler[0]->render() === 'ok');

$threw = false;

try
{
    Web::resolveHandler(['NexusWP\\Missing\\Controller','render']);
}
catch (InvalidArgumentException $exception)
{
    $threw = true;
}

check('route handler rejects a missing class', $threw);

// --- job registration is deferred until init --------------------------------

$GLOBALS['wp_scheduled'] = [];

function wp_next_scheduled(string $hook, array $args = []): int|false
{
    return $GLOBALS['wp_scheduled'][$hook] ?? false;
}

function wp_schedule_event(int $timestamp, string $recurrence, string $hook, array $args = []): bool
{
    $GLOBALS['wp_scheduled'][$hook] = $timestamp;
    return true;
}

function wp_clear_scheduled_hook(string $hook, array $args = []): int
{
    unset($GLOBALS['wp_scheduled'][$hook]);
    return 1;
}

class TestJob extends \nexuswp\core\job\AbstractJob
{
    public static int $constructed = 0;
    public static int $handled = 0;

    public string $hook = 'test_job_hook';

    public function __construct(array $args = [])
    {
        parent::__construct($args);
        self::$constructed++;
    }

    public function handle(...$args): void
    {
        self::$handled++;
    }
}

$scheduler = new \nexuswp\core\job\JobScheduler();
$scheduler->registerJob(TestJob::class);

check('job class is not loaded when registered', TestJob::$constructed === 0);
check('job registration schedules nothing before init', $GLOBALS['wp_scheduled'] === []);

($GLOBALS['wp_hooks']['init'])();

check('job is built on init', TestJob::$constructed === 1);
check('job is scheduled on init', isset($GLOBALS['wp_scheduled']['test_job_hook']));

// --- rewrite routing keeps a constant hook count ----------------------------

Web::registerRule('app', [TestController::class, 'render']);
$hookCountAfterFirst = $GLOBALS['wp_hook_count'];

for ($i = 0; $i < 40; $i++)
    Web::registerRule('page-'.$i, [TestController::class, 'render']);

check('registering more routes adds no extra hooks', $GLOBALS['wp_hook_count'] === $hookCountAfterFirst);

$GLOBALS['wp_query_vars'] = ['page-1' => 1];

function get_query_var(string $name, mixed $default = ''): mixed
{
    return $GLOBALS['wp_query_vars'][$name] ?? $default;
}

check('only the matching rewrite rule runs', Web::handleRequest() === 'ok');

$GLOBALS['wp_query_vars'] = [];
check('unmatched requests resolve no handler', Web::handleRequest() === null);

echo "\n".($failures === 0 ? 'All checks passed.' : "{$failures} check(s) failed.")."\n";
exit($failures === 0 ? 0 : 1);
