<?php

class UpdateSubscritionUserId extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->table('subscriptions', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC',
        ])
            ->removeIndexByName('user_id')
            ->addIndex(['users_id'], [
                'name' => 'user_id',
                'unique' => false,
            ])
            ->save();
    }
}
