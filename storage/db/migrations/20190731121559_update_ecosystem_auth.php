<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdateEcosystemAuth extends AbstractMigration
{
    public function change()
    {
        $this->table('apps', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => '',
                'row_format' => 'COMPACT',
            ])
            ->addColumn('ecosystem_auth', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'payments_active',
            ])
            ->removeColumn('ecosystem_login')
            ->save();
    }
}
