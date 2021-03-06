<?php

class ChangeEntityIdToVarcharOnFilesystemEntities extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('filesystem_entities', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->changeColumn('entity_id', 'char', [
                'null' => false,
                'default' => '',
                'limit' => 36,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'filesystem_id',
            ])
            ->save();
    }
}
