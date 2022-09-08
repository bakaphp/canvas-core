<?php


class UpdateAppPlansNameColumn extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('apps_plans', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->renameColumn('payment_ìnterval', 'payment_interval')
            ->save();
    }
}
