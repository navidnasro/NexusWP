<?php

// WordPress only includes this file when the plugin is deleted from the admin
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit;

// the schema is left in place on uninstall so re-installing does not lose data.
// drop the plugin tables manually when a full reset is needed.
$options = [
    'nexuswp_flush_rewrite_rules',
    'nexuswp_jobs_need_explicit_run',
    'nexuswp_last_product_sync',
];

foreach ($options as $option)
    delete_option($option);

$logFiles = glob(__DIR__.'/storage/logs/log-*.json');

if ($logFiles)
    foreach ($logFiles as $logFile)
        unlink($logFile);
