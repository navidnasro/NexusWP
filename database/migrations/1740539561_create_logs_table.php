<?php

namespace jetstore\database\migrations;

use jetstore\core\database\migration\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->prefix}logs (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NULL,
            `message` VARCHAR(255) NOT NULL,
            `ip` VARCHAR(255) NOT NULL,
            `type` VARCHAR(255) NOT NULL,
            `file` VARCHAR(255) NOT NULL,
            FOREIGN KEY (user_id) REFERENCES {$this->prefix}users(id) ON DELETE CASCADE,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) {$this->charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function down(): void
    {
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->prefix}logs");
    }
};