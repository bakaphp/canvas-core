<?php
declare(strict_types=1);

namespace Canvas\Models;

class UsersAssociatedCompanies extends \Baka\Auth\Models\UsersAssociatedCompany
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
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->setSource('users_associated_company');
    }
}
