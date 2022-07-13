<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddingNotificationGroup extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('notifications', [
            'id' => false, 
            'primary_key' => ['id'], 
            'engine' => 'InnoDB', 
            'encoding' => 'utf8mb4', 
            'collation' => 
            'utf8mb4_unicode_ci', 
            'comment' => '', 
            'row_format' => 'Dynamic'
        ])
        ->addColumn('group', 'text', [
            'null' => true,
            'default' => null,
            'limit' => MysqlAdapter::TEXT_LONG,
            'collation' => 'utf8mb4_unicode_520_ci',
            'encoding' => 'utf8mb4',
        ])
        ->save();
    }
}
