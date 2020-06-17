<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class UserRoles extends AbstractModel
{
    public int $users_id;
    public int $apps_id;
    public int $roles_id;
    public int $companies_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('user_roles');

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->belongsTo(
            'roles_id',
            'Canvas\Models\Roles',
            'id',
            ['alias' => 'roles']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );
    }

    /**
     * Validations and business logic.
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            ['users_id', 'apps_id', 'companies_id'],
            new Uniqueness(
                [
                    'message' => 'Can\'t have 2 roles for the same company on the same app - ' . $this->company->name
                ]
            )
        );

        return $this->validate($validator);
    }
}
