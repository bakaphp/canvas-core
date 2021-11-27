<?php


class UpdateConfigSettingsComments extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('companies_settings', [
            'id' => false,
            'primary_key' => ['companies_id', 'name'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->changeColumn('companies_id', 'integer', [
                'comment' => '',
            ])
            ->save();
    }
}
