<?php

use Phinx\Db\Adapter\MysqlAdapter;

class RemovePrimaryUsersLinkedSources extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('user_linked_sources', [
                'id' => false,
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->save();
    }
}
