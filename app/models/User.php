<?php

namespace nexuswp\app\models;

use nexuswp\core\database\model\Model;

/**
 * Application user.
 *
 * The table name is unprefixed: Model adds the WordPress prefix and the
 * plugin's configured table prefix at query time.
 */
class User extends Model
{
    protected static string $table = 'users';

    public static function findByPhone(string $phone): ?User
    {
        $row = self::getDB()->get_row(
            self::getDB()->prepare(
                "SELECT * FROM " . static::getTable() . " WHERE phone_number = %s",
                $phone
            )
        );

        return $row ? static::hydrate($row) : null;
    }
}
