<?php

namespace nexuswp\app\jobs;

use nexuswp\core\job\AbstractJob;

/**
 * Recurring job example.
 *
 * Register it once in jobs/schedules.php:
 *   $scheduler = new JobScheduler();
 *   $scheduler->registerJobs([SyncProductsJob::class => ['source' => 'warehouse']]);
 */
class SyncProductsJob extends AbstractJob
{
    public string $hook = 'nexuswp_sync_products';

    public array $interval = [
        'name'     => 'nexuswp_every_five_minutes',
        'interval' => 300,
        'display'  => 'Every 5 minutes',
    ];

    public function handle(...$args): void
    {
        $source = $args['source'] ?? $args[0] ?? 'default';

        // replace with the real sync routine
        update_option('nexuswp_last_product_sync', [
            'source' => $source,
            'at'     => time(),
        ]);
    }
}
