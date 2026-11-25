<?php

use nexuswp\app\hooks\DisableEmojis;
use nexuswp\core\hooks\HookManager;

// class-strings are registered lazily: DisableEmojis is only autoloaded and
// constructed if the `init` hook actually fires
HookManager::action('init', DisableEmojis::class, 20);
