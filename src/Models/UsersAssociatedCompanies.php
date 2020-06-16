<?php
declare(strict_types=1);

namespace Canvas\Models;

class UsersAssociatedCompanies extends \Baka\Auth\Models\UsersAssociatedCompany
{
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
