<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateSystemModuleMaybe extends AbstractMigration
{
    public function change()
    {
        // execute()
        $stmt = $this->query('SHOW COLUMNS FROM system_modules WHERE FIELD LIKE "%browse_fields%"'); // returns the number of affected rows

        $rows = $stmt->fetchAll(); // returns the result as an array

        //work around for old canvas instance running with the old migration
        if (empty($rows)) {
            $table = $this->table('system_modules', ['id' => false, 'primary_key' => ['id'], 'engine' => 'InnoDB', 'encoding' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'comment' => 'list of modules , user can interact on each of the diff apps', 'row_format' => 'Dynamic']);
            $table
            ->addColumn('show', 'integer', ['null' => true, 'default' => '1', 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'menu_order'])
            ->addColumn('use_elastic', 'boolean', ['null' => true, 'default' => '0', 'limit' => MysqlAdapter::INT_TINY, 'precision' => 3, 'after' => 'show'])
            ->addColumn('browse_fields', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_LONG, 'precision' => 3, 'after' => 'use_elastic'])
            ->save();
        }
    }
}
