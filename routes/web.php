<?php

use nexuswp\app\controllers\AppController;
use nexuswp\core\route\Web;

// the controller is resolved only when the rewrite rule matches
Web::registerRule('app', [AppController::class, 'render']);
