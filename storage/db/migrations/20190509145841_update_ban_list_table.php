<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateBanListTable extends AbstractMigration
{
    public function change()
    {
        $this->table('banlist', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_bin',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
        ->changeColumn('id', 'integer', [
                'null' => false,
                'limit' => '7',
                'signed' => false,
                'identity' => 'enable',
            ])
        ->changeColumn('users_id', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => '19',
                'after' => 'id',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'email',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addColumn('is_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
            ->save();
    }
}
