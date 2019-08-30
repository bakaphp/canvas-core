<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class PrimaryKeyFileEntities extends AbstractMigration
{
    public function change()
    {
        $this->execute('ALTER TABLE filesystem_entities DROP PRIMARY KEY;');
        $this->execute('ALTER TABLE `filesystem_entities`
        ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (`id`);');

        $this->table('filesystem_entities', [
                'id' => false,
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
        ->changeColumn('filesystem_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'id',
            ])
        ->changeColumn('entity_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'filesystem_id',
            ])
        ->changeColumn('companies_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'entity_id',
            ])
        ->changeColumn('system_modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'companies_id',
            ])
        ->changeColumn('field_name', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'system_modules_id',
            ])
        ->changeColumn('created_at', 'datetime', [
                'null' => false,
                'after' => 'field_name',
            ])
        ->changeColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
        ->changeColumn('is_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'updated_at',
            ])
        ->addIndex(['filesystem_id', 'entity_id', 'companies_id', 'system_modules_id'], [
                'name' => 'uniqueentityfilesytem',
                'unique' => true,
            ])
            ->save();
    }
}
