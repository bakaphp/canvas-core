<?php

use Phinx\Db\Adapter\MysqlAdapter;

class editGroupNotifications extends Phinx\Migration\AbstractMigration
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
        ->renameColumn('group', 'content_group')
        ->save();
    }
}
