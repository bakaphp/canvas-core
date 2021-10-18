<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateLinkedSourceWithDatesCreation extends AbstractMigration
{
    public function change()
    {
        $this->table('user_linked_sources', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('created_at', 'datetime', [
                'null' => true,
                'after' => 'source_username',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
        ->changeColumn('is_deleted', 'boolean', [
            'null' => true,
            'default' => '0',
            'limit' => MysqlAdapter::INT_TINY,
            'after' => 'updated_at',
        ])
            ->save();
    }
}
