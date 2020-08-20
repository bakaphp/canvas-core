<?php

use Phinx\Db\Adapter\MysqlAdapter;

class AddUniqueConstraitForTableSystemModulesForms extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('system_modules_forms', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addIndex(['apps_id', 'companies_id', 'name', 'slug'], [
                'name' => 'system_modules_forms_UN',
                'unique' => true,
            ])
            ->save();
    }
}
