<?php

use Phinx\Seed\AbstractSeed;

class FileSystemSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'companies_id' => 3,
                'apps_id' => 1,
                'users_id' => 1,
                'name' => 'logo.jpg',
                'path' => 'Example Path',
                'url' => 'Example Url',
                'file_type' => 'jpg',
                'size' => '10',
                'created_at' => date('Y-m-d H:m:s'),
                'is_deleted' => 0
            ]
        ];

        $posts = $this->table('filesystem');
        $posts->insert($data)
              ->save();
    }
}
