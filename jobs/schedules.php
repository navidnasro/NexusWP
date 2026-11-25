<?php

use nexuswp\core\job\AbstractJob;
use nexuswp\core\job\JobDispatcher;
use nexuswp\core\job\JobScheduler;

// recurring jobs: registered once, then WordPress cron keeps the schedule.
// the bundled job is disabled by default so a fresh install schedules nothing.
//
// $scheduler = new JobScheduler();
// $scheduler->registerJobs([
//     \nexuswp\app\jobs\SyncProductsJob::class => ['source' => 'warehouse'],
// ]);

// one-off run, queued through WP-Cron instead of running inside the request
// JobDispatcher::dispatch(\nexuswp\app\jobs\SyncProductsJob::class);

// stored job hooks only matter while cron is running: skipping the call here
// keeps normal page loads and REST requests from touching the registry option
$runningCron = (defined('DOING_CRON') && DOING_CRON)
    || (function_exists('wp_doing_cron') && wp_doing_cron());

if ($runningCron)
    AbstractJob::restoreRegisteredActions();
