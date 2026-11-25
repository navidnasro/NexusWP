<?php

namespace nexuswp\bootstraps;

use nexuswp\core\Config;
use nexuswp\core\database\migration\MigrationManager;
use nexuswp\core\database\seeder\SeederManager;
use nexuswp\Environment;

class App
{
    private static ?App $instance = null;
    private static bool $booted = false;

    public static function getPlugin(): App
    {
        if (is_null(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }

    public function init(): void
    {
        $this->boot();

        register_activation_hook(Environment::PluginFile, [$this,'activate']);
        register_deactivation_hook(Environment::PluginFile, [$this,'deactivate']);

        // rewrite rules can only be flushed after every rule was registered on init
        add_action('init', [$this,'flushPendingRewriteRules'], 99);
    }

    /**
     * hands the plugin environment to the core package. the core never reads
     * plugin classes, so this is the single place both sides meet.
     */
    public function boot(): void
    {
        if (self::$booted)
            return;

        Config::init([
            'pluginRoot'    => Environment::PluginRoot,
            'tablePrefix'   => Environment::TablePreFix,
            'storagePath'   => Environment::Storage,
            'publicPath'    => Environment::PublicFolder,
            'textDomain'    => Environment::TextDomain,
            'authKey'       => defined('AUTH_KEY') ? AUTH_KEY : '',
            'migrationPath' => Environment::PluginRoot.'/database/migrations',
        ]);

        self::$booted = true;
    }

    public function activate(): void
    {
        $this->boot();
        $this->runMigrations();

        // autoloaded on purpose: the flag is read on the next request and must
        // not add a separate option query to every page load
        update_option('nexuswp_flush_rewrite_rules','yes');
    }

    public function deactivate(): void
    {
        delete_option('nexuswp_flush_rewrite_rules');
        flush_rewrite_rules(false);
    }

    public function flushPendingRewriteRules(): void
    {
        if (get_option('nexuswp_flush_rewrite_rules') !== 'yes')
            return;

        flush_rewrite_rules(false);
        delete_option('nexuswp_flush_rewrite_rules');
    }

    public function use(string $system): App
    {
        switch ($system)
        {
            case 'api':
                require_once Environment::PluginRoot.'/routes/api.php';
                break;

            case 'web':
                require_once Environment::PluginRoot.'/routes/web.php';
                break;

            case 'job':
                require_once Environment::PluginRoot.'/jobs/schedules.php';
                break;

            case 'hook':
                require_once Environment::PluginRoot.'/hooks/register.php';
                break;

            case 'event':
                require_once Environment::PluginRoot.'/events/register.php';
                break;
        }

        return $this;
    }

    public function runMigrations(): void
    {
        $migrationManager = new MigrationManager();
        $migrationManager->runMigrations();

        // seeders are opt-in, so activation never inserts demo data by accident
        if (defined('NEXUSWP_RUN_SEEDERS') && NEXUSWP_RUN_SEEDERS)
        {
            $seederManager = new SeederManager();
            $seederManager->runSeeders();
        }
    }

    public function rollbackMigrations(): void
    {
        $migrationManager = new MigrationManager();
        $migrationManager->rollbackAllMigrations();
    }

}
