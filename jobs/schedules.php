<?php

use jetstore\core\job\AbstractJob;

// Schedule repetitive jobs
//$scheduler = new JobScheduler();
//$scheduler->registerJobs(
//    [
//        ExampleJob::class => ['arg1','arg2','arg3'],
//    ]
//);

// Schedule single run jobs, background processes
//JobDispatcher::dispatch(ExampleJob::class);
//JobDispatcher::dispatch(ExampleJob::class, 'optionalParam4', 'optionalParam3');

$reservedActions = get_option('cronjobs_that_need_explicit_run');

if ($reservedActions)
{
    foreach ($reservedActions as $action)
    {
        $hook = $action['hook'];
        $class = $action['class'];
        $method = $action['method'];
        $argsCount = $action['argsCount'];

        $object = new $class();

        if ($object instanceof AbstractJob)
            add_action($hook, [$object, $method], 10, $argsCount);
    }
}