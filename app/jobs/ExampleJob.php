<?php

namespace jetstore\app\jobs;

use jetstore\core\job\AbstractJob;

class ExampleJob extends AbstractJob
{
    public bool $run = false;
    public string $hook = 'sync_products_job_singl2365';
    public array $interval = [
        'name' => 'every_five_minutes',
        'interval' => 300,
        'display' => 'Every 5 Minutes'
    ];

    public function handle(...$args): void
    {
        update_option('job_system_log',$args);
        // Actual sync logic here
    }
}
