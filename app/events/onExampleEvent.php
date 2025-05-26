<?php

namespace jetstore\app\events;

use jetstore\core\events\Event;

class onExampleEvent extends Event
{
    public string $eventName = 'userRegistered';
    public bool $trigger = false;

    public function handle(...$args): void
    {
        update_option('testing_event_'.$this->eventName, 'params '.implode(',', $args));
    }
}