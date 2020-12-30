<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddMenusAndMenusLinksTables extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('menus', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('apps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'apps_id',
            ])
            ->addColumn('name', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'companies_id',
            ])
            ->addColumn('slug', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'name',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'slug',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->create();
        $this->table('menus_links', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('menus_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
            ->addColumn('parent_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'menus_id',
            ])
            ->addColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'parent_id',
            ])
            ->addColumn('url', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'system_modules_id',
            ])
            ->addColumn('title', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'url',
            ])
            ->addColumn('position', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_SMALL,
                'after' => 'title',
            ])
            ->addColumn('icon_url', 'string', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_SMALL,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'position',
            ])
            ->addColumn('icon_class', 'string', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_SMALL,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'icon_url',
            ])
            ->addColumn('route', 'string', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_SMALL,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'icon_class',
            ])
            ->addColumn('is_published', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'route',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'is_published',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => '1',
                'after' => 'updated_at',
            ])
            ->create();
    }
}
