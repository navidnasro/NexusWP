<?php

namespace jetstore\bootstraps;

use jetstore\app\controllers\AppController;
use jetstore\core\database\migration\MigrationManager;
use jetstore\core\route\Web;
use jetstore\Environment;

class App
{
    private static ?App $instance = null;

    public static function getPlugin(): App
    {
        if (is_null(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }

    public function init(): void
    {
        register_activation_hook(Environment::PluginFile, [$this,'runMigrations']);
//        register_deactivation_hook(Environment::PluginFile, [$this,'rollbackMigrations']);
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
//        $migrationManager = new MigrationManager();
//        $migrationManager->runMigrations();

//        $seederManager = new SeederManager();
//        $seederManager->runSeeders();
    }

    public function rollbackMigrations(): void
    {
        $migrationManager = new MigrationManager();
        $migrationManager->rollbackAllMigrations();
    }
}