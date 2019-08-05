<?php
declare(strict_types=1);

namespace Canvas\Models;

class UsersAssociatedApps extends AbstractModel
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
    public $companies_id;

    /**
     *
     * @var string
     */
    public $identify_id;

    /**
     *
     * @var integer
     */
    public $user_active;

    /**
     *
     * @var string
     */
    public $user_role;

    /**
     *
     * @var string
     */
    public $password;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->setSource('users_associated_apps');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'users_associated_apps';
    }
}
