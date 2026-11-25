<?php

namespace nexuswp\app\models;

use nexuswp\core\database\model\Model;

/**
 * Application log entry, kept for audit trails rather than debugging output.
 */
class Log extends Model
{
    protected static string $table = 'logs';
}
