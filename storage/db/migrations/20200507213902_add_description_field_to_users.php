<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddDescriptionFieldToUsers extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->addColumn('description', 'text', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::TEXT_MEDIUM,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'lastname',
            ])
            ->save();
    }
}
