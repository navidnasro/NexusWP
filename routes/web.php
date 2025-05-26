<?php

use jetstore\app\controllers\AppController;
use jetstore\core\route\Web;

Web::registerRule('shop-management', [new AppController(), 'render']);

if (!get_option('plugin_rewrite_rules_are_flushed'))
{
    flush_rewrite_rules();
    update_option('plugin_rewrite_rules_are_flushed', 'yes');
}
