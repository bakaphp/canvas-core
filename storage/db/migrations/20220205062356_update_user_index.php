<?php


class UpdateUserIndex extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('users', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['displayname'], [
                'name' => 'displayname',
                'unique' => false,
            ])
            ->save();
        $this->table('users_invite', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->addIndex(['email'], [
                'name' => 'email',
                'unique' => false,
            ])
            ->save();
    }
}
