<?php

use Phinx\Db\Adapter\MysqlAdapter;

class ChangeEntityIdToVarcharOnFilesystemEntities extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8';");
        $this->execute("ALTER DATABASE COLLATE='utf8_general_mysql500_ci';");
        $this->table('filesystem_entities', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->changeColumn('entity_id', 'string', [
                'null' => false,
                'default' => '\'\'',
                'limit' => 255,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'filesystem_id',
            ])
            ->save();
    }
}
