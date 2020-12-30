<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AppByDomain extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('apps', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('domain', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'url',
            ])
            ->changeColumn('default_apps_plan_id', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'domain',
            ])
            ->changeColumn('is_actived', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'default_apps_plan_id',
            ])
            ->changeColumn('key', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 128,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'is_actived',
            ])
            ->changeColumn('payments_active', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'key',
            ])
            ->changeColumn('ecosystem_auth', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'payments_active',
            ])
            ->changeColumn('is_public', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'ecosystem_auth',
            ])
            ->addColumn('domain_based', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'is_public',
            ])
            ->changeColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'domain_based',
            ])
            ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->changeColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => '10',
                'after' => 'updated_at',
            ])
            ->addIndex(['domain_based'], [
                'name' => 'domain_based',
                'unique' => false,
            ])
            ->addIndex(['is_public'], [
                'name' => 'is_public',
                'unique' => false,
            ])
            ->addIndex(['ecosystem_auth'], [
                'name' => 'ecosystem_auth',
                'unique' => false,
            ])
            ->addIndex(['payments_active'], [
                'name' => 'payments_active',
                'unique' => false,
            ])
            ->addIndex(['is_actived'], [
                'name' => 'is_actived',
                'unique' => false,
            ])
            ->addIndex(['name'], [
                'name' => 'name',
                'unique' => false,
            ])
            ->addIndex(['domain'], [
                'name' => 'domain',
                'unique' => false,
            ])
            ->addIndex(['is_deleted'], [
                'name' => 'is_deleted',
                'unique' => false,
            ])
            ->addIndex(['domain', 'domain_based'], [
                'name' => 'domain_domain_based',
                'unique' => false,
            ])
            ->save();
    }
}
