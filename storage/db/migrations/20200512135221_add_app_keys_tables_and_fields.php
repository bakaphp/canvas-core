<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddAppKeysTablesAndFields extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('apps_keys', [
                'id' => false,
                'primary_key' => ['apps_id', 'users_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('client_id', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
            ])
            ->addColumn('client_secret_id', 'string', [
                'null' => false,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'client_id',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'client_secret_id',
            ])
            ->addColumn('users_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
            ->addColumn('last_used_date', 'datetime', [
                'null' => true,
                'after' => 'users_id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'last_used_date',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->addIndex(['apps_id', 'users_id'], [
                'name' => 'apps_keys_UN',
                'unique' => true,
            ])
            ->create();
    }
}
