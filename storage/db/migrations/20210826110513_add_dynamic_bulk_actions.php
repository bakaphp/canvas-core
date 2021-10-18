<?php


class AddDynamicBulkActions extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('system_modules', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'list of modules , user can interact on each of the diff apps',
            'row_format' => 'DYNAMIC',
        ])
            ->addColumn('bulk_actions', 'text', [
                'null' => true,
                'collation' => 'utf8mb4_unicode_ci',
                'encoding' => 'utf8mb4',
                'after' => 'browse_fields',
            ])
            ->save();
    }
}
