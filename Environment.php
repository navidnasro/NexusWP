<?php

namespace nexuswp;

/**
 * Static plugin environment.
 *
 * The core package never reads this class: values it needs are handed over
 * once in bootstraps\App::boot() and read there through core\Config.
 */
final class Environment
{
    const PluginRoot = __DIR__;
    const PluginFile = __DIR__.'/NexusWP.php';
    const PublicFolder = __DIR__.'/public';
    const Storage = __DIR__.'/storage';
    const Views = __DIR__.'/app/views';
    const IMG = RootUrl.'/public/assets/img';
    const CSS = RootUrl.'/public/assets/css';
    const JS = RootUrl.'/public/assets/js';
    const TextDomain = 'nexuswp';

    // database credentials exposed by WordPress
    const DbName = DB_NAME;
    const DbUser = DB_USER;
    const DbPassword = DB_PASSWORD;
    const DbHost = DB_HOST;
    const DbCharset = DB_CHARSET;

    /**
     * Tables are created with the WordPress prefix plus this value, so the
     * plugin never collides with another install of the same framework.
     */
    const TablePreFix = 'nexuswp_';

    const TokenTtl = 86400; // 24 hours
}
