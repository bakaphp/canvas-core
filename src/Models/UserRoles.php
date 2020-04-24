<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class UserRoles extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $roles_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;

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
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'user_roles';
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
