<?php
declare(strict_types=1);

namespace Canvas\Models;

class AppsRoles extends \Baka\Auth\Models\AppsRoles
{
    public int $apps_id;
    public string $roles_name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('apps_roles');

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );
    }
}
