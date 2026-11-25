<?php

use nexuswp\app\events\UserRegistered;
use nexuswp\core\events\EventListener;

// listeners are stored by class name and only constructed when the event fires
EventListener::on('user.registered', UserRegistered::class);
