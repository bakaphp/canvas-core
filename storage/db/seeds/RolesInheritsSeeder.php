<?php

use Phinx\Seed\AbstractSeed;

class RolesInheritsSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'roles_name' => 'Users',
                'roles_id'=>1,
                'roles_inherit'=>1
            ]
        ];

        $posts = $this->table('roles_inherits');
        $posts->insert($data)
              ->save();
    }
}
