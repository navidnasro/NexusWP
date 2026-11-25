<?php

namespace nexuswp\database\migrations;

use nexuswp\core\database\migration\Migration;

return new class extends Migration
{
    private array $indexes = [
        'logs'  => ['user_id', 'created_at'],
        'users' => ['role'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $columns)
            foreach ($columns as $column)
                $this->addIndex($table,$column);
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $columns)
            foreach ($columns as $column)
                $this->dropIndex($table,$column);
    }

    private function addIndex(string $table, string $column): void
    {
        if ($this->indexExists($table,$column))
            return;

        $tableName = $this->prefix.$table;
        $indexName = $this->indexName($table,$column);

        $this->wpdb->query("ALTER TABLE {$tableName} ADD INDEX {$indexName} ({$column})");
    }

    private function dropIndex(string $table, string $column): void
    {
        if (!$this->indexExists($table,$column))
            return;

        $tableName = $this->prefix.$table;
        $indexName = $this->indexName($table,$column);

        $this->wpdb->query("ALTER TABLE {$tableName} DROP INDEX {$indexName}");
    }

    private function indexExists(string $table, string $column): bool
    {
        $tableName = $this->prefix.$table;
        $indexName = $this->indexName($table,$column);

        $result = $this->wpdb->get_var(
            $this->wpdb->prepare("SHOW INDEX FROM {$tableName} WHERE Key_name = %s",$indexName)
        );

        return !empty($result);
    }

    private function indexName(string $table, string $column): string
    {
        return 'idx_'.$table.'_'.$column;
    }
};
