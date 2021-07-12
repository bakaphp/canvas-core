<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;

class AppsRoles extends Model
{
    public int $apps_id;
    public string $roles_name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('apps_roles');

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Auth\Models\Apps',
            'id',
            ['alias' => 'appAuth']
        );
    }
}
