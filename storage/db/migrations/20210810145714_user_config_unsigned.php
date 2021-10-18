<?php


class UserConfigUnsigned extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('user_config', [
            'id' => false,
            'primary_key' => ['users_id', 'name'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'COMPACT',
        ])
            ->changeColumn('users_id', 'integer', [
                'null' => false,
                'limit' => '20',
            ])
            ->save();
    }
}
