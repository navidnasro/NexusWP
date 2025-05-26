<?php

use jetstore\app\events\onExampleEvent;
use jetstore\core\events\EventListener;

EventListener::register(new onExampleEvent());
