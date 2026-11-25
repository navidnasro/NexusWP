<?php
/*
 * Plugin Name:       NexusWP
 * Plugin URI:        https://github.com/navidnasro/NexusWP
 * Description:       WordPress plugin framework with routing, models, migrations and a Vue front end.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            NavidNasro
 * Author URI:        https://github.com/navidnasro
 * License:           MIT
 * License URI:       https://mit-license.org/
 * Update URI:        https://github.com/navidnasro/NexusWP
 * Text Domain:       nexuswp
 * Domain Path:       /languages
 */

defined('ABSPATH') || exit;

use nexuswp\bootstraps\App;

if (!defined('RootUrl'))
    define('RootUrl', plugin_dir_url(__FILE__));

if (!defined('RootPath'))
    define('RootPath', plugin_dir_path(__FILE__));

if (!defined('SiteUrl'))
    define('SiteUrl', get_site_url());

$autoloader = __DIR__.'/vendor/autoload.php';

if (is_file($autoloader))
    require_once $autoloader;

if (!class_exists(App::class))
{
    add_action('admin_notices', static function (): void
    {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('NexusWP could not start: run "composer install" in the plugin directory.','nexuswp');
        echo '</p></div>';
    });

    return;
}

App::getPlugin()
    ->use('api')
    ->use('web')
    ->use('hook')
    ->use('job')
    ->use('event')
    ->init();
