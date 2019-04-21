<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UpdatePlanAclDefaultPlan extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('apps');
        $table->addColumn('default_apps_plan_id', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_REGULAR, 'precision' => 10, 'after' => 'url'])
            ->update();
    }
}
