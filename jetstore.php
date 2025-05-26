<?php
/*
 * Plugin Name:       JetStore
 * Plugin URI:        https://github.com/navidnasro
 * Description:       سامانه مدیریت فروشگاه jetstore به همراه اپلیکیشن
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            NavidNasro
 * Author URI:        https://github.com/navidnasro
 * License:           MIT
 * License URI:       https://mit-license.org/
 * Update URI:        https://github.com/navidnasro
 * Text Domain:       jetstore
 * Domain Path:       /languages
 */

use jetstore\bootstraps\App;

define('RootUrl', plugin_dir_url(__FILE__));
define('RootPath', plugin_dir_path(__FILE__));
define('SiteUrl', get_site_url());

if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once __DIR__.'/vendor/autoload.php';

App::getPlugin()
    ->use('api')
    ->use('web')
    ->use('hook')
    ->use('job')
    ->use('event')
    ->init();
