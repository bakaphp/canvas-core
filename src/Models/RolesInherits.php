<?php
declare(strict_types=1);

namespace Canvas\Models;

class RolesInherits extends AbstractModel
{

    public int $roles_id;
    public int $roles_inherit;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('roles_inherits');

        $this->belongsTo(
            'roles_id',
            'Canvas\Models\Roles',
            'id',
            ['alias' => 'role']
        );
    }

}
