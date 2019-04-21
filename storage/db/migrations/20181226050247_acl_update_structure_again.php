<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class AclUpdateStructureAgain extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('companies', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => "\t\t\t\t\t",
                'row_format' => 'Compact',
            ]);
        $table->addColumn('has_activities', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'precision' => '3',
                'after' => 'users_id',
            ])
            ->save();

        $table = $this->table('users', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'Compact',
            ]);
        $table->changeColumn('phone_number', 'string', [
                'null' => true,
                'default' => '"America/New_York"',
                'limit' => 128,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'timezone',
            ])
        ->changeColumn('cell_phone_number', 'string', [
                'null' => true,
                'default' => '"America/New_York"',
                'limit' => 128,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'phone_number',
            ])
            ->save();
    }
}
