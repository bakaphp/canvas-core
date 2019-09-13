<?php

use Phinx\Seed\AbstractSeed;

class FileSystemEntitiesSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'filesystem_id' => 1,
                'entity_id' => 3,
                'system_modules_id' => 1,
                'companies_id' => 3,
                'field_name' => 'logo',
                'created_at' => date('Y-m-d H:m:s'),
            ]
        ];

        $posts = $this->table('filesystem_entities');
        $posts->insert($data)
              ->save();
    }
}
